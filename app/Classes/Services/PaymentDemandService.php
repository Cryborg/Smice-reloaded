<?php

namespace App\Classes\Services;

use App\Models\Payment;
use App\Models\TransferHistory;
use App\Models\SepaFile;
use App\Models\VoucherFile;
use Carbon\Carbon;
use Digitick\Sepa\DomBuilder\DomBuilderFactory;
use Digitick\Sepa\GroupHeader;
use Digitick\Sepa\PaymentInformation;
use Digitick\Sepa\TransferFile\CustomerCreditTransferFile;
use Digitick\Sepa\TransferInformation\CustomerCreditTransferInformation;
use Illuminate\Support\Facades\Validator;
use App\Exceptions\SmiceException;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class PaymentDemandService
{
    public function generateXML($params, $user_id)
    {
        $payment_id = array_get($params, 'payment_ids');

       

        Validator::make(
            [
                'payment_ids' => $payment_id,
                //'filename'    => $filename
            ],
            [
                'payment_ids' => 'int_array|required',
                //'filename'    => 'string|required'
            ]
        )->passOrDie();

        $total      = 0;
        $total_user = [];
        $tab        = [];
        $operations = count($payment_id);

        $payments = Payment::whereIn('id', $payment_id)->get();

        $payments->each(function($item, $key) use (&$tab, &$total_user, &$total) {
            $gains           = $item->gains->sum('amount');
            $tab['username'] = $item->user->first_name . ' ' . $item->user->last_name;
            $tab['bic']      = $item->user->bic;
            $tab['iban']     = $item->user->iban;
            $tab['gains']    = intval($gains*100);
            $tab['date']     = $item->ask_payment_date;
            $total += $gains;

            array_push($total_user, $tab);
        });
        $SampleUniqueMsgId = "SEPAWIN" . date("YdmHis") . "-" . rand(10, 20);
        ;
        //Set the initial information
        $groupHeader = new GroupHeader($SampleUniqueMsgId, 'SMICE');
        $sepaFile = new CustomerCreditTransferFile($groupHeader);
        // Create a PaymentInformation the Transfer belongs to
        $iban = env('SEPA_IBAN');
        $bic = env('SEPA_BIC');
        $account = env('SEPA_ACCOUNT');

        $payment = new PaymentInformation(
            $SampleUniqueMsgId,
            $iban, // IBAN the money is transferred from
            $bic,  // BIC
            $account // Debitor Name
        );


        foreach ($total_user as $value) {
            $transfer = new CustomerCreditTransferInformation(
                $value['gains'], // Amount
                str_replace(' ','',$value['iban']), //IBAN of creditor
                $value['username'] //Name of Creditor
            );
            $transfer->setBic(str_replace(' ','',$value['bic'])); // Set the BIC explicitly
            $transfer->setRemittanceInformation('Remboursement SMICE');
            $payment->addTransfer($transfer);

        }
        // It's possible to add multiple payments to one SEPA File
        $sepaFile->addPaymentInformation($payment);

        // Attach a dombuilder to the sepaFile to create the XML output
        //$domBuilder = DomBuilderFactory::createDomBuilder($sepaFile);

        // Or if you want to use the format 'pain.001.001.03' instead
        $domBuilder = DomBuilderFactory::createDomBuilder($sepaFile, 'pain.001.001.03');

        //echo($domBuilder->asXml());
        $SepaFile = new SepaFile();
        $SepaFile->filename = $SampleUniqueMsgId;
        $SepaFile->transactions = $operations;
        $SepaFile->amount = $total;
        $SepaFile->created_by = $user_id;
        $SepaFile->created_at = Carbon::now();
        $SepaFile->updated_at = Carbon::now();
        $SepaFile->status = TransferHistory::$status[0];
        $SepaFile->save();
            Payment::whereIn('id', $payment_id)->update(['sepa_file_id' => $SepaFile->id]);
            $result = [
                'name' => $SampleUniqueMsgId, // storage_path('app/public/sepa/')
                'transaction' => $groupHeader->getNumberOfTransactions(),
                'amount' => $total,
                'data' => $domBuilder->asXml(),
                'created_by' => $user_id,
            ];
            return $result;
    }

    public function getVoucherFile(array $params)
    {
        $id = array_get($params, 'id');
        Validator::make(
            [
                'id' => $id,
            ],
            [
                'id' => 'int|required',
            ]
        )->passOrDie();

        $voucher_file = VoucherFile::where('id', $id)->first();
        $path = '/public/wedoogift/' . $voucher_file->filename . '.xlsx';
        if (!Storage::exists($path)) {
            throw new SmiceException(
                SmiceException::HTTP_NOT_ACCEPTABLE,
                SmiceException::E_RESOURCE,
                'XLSX file not found.'
            );
        }
        $response = new Response();
        $response->setContent(Storage::get($path));
        $response->header('Content-type', Storage::getMimeType($path));

        return $response;
    }

    public function getSepaXmlFile(array $params)
    {
        $id = array_get($params, 'id');
        Validator::make(
            [
                'id' => $id,
            ],
            [
                'id' => 'int|required',
            ]
        )->passOrDie();

        $sepa_file = SepaFile::where('id', $id)->first();
        $path = '/public/sepa/' . $sepa_file->filename . '.xml';
        if (!Storage::exists($path)) {
            throw new SmiceException(
                SmiceException::HTTP_NOT_ACCEPTABLE,
                SmiceException::E_RESOURCE,
                'XML file not found.'
            );
        }

        $response = new Response();
        $response->setContent(Storage::get($path));
        $response->header('Content-type', 'text/xml');

        return $response;
    }

    public function getDpaeXmlFile(string $filename)
    {
        $path = '/public/dpae/' . $filename . '.xml';

        if (!Storage::exists($path)) {
            throw new SmiceException(
                SmiceException::HTTP_NOT_ACCEPTABLE,
                SmiceException::E_RESOURCE,
                'XML file not found.'
            );
        }
        $response = new Response();
        $response->setContent(Storage::get($path));
        $response->header('Content-type', 'text/xml');

        return $response;
    }

    public function getPayslipXlsxFile(string $filename)
    {
        $path = '/public/xlsx/' . $filename . '.xlsx';

        if (!Storage::exists($path)) {
            throw new SmiceException(
                SmiceException::HTTP_NOT_ACCEPTABLE,
                SmiceException::E_RESOURCE,
                'Xlsx file not found.'
            );
        }
        $response = new Response();
        $response->setContent(Storage::get($path));
        $response->header('Content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        return $response;
    }
}