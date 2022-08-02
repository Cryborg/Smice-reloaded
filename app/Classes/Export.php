<?php
namespace App\Classes;

use App\Http\Country\Models\Country;
use App\Models\Axe;
use App\Models\Civility;
use App\Models\CriteriaA;
use App\Models\CriteriaB;
use App\Models\Job;
use App\Models\Language;
use App\Models\Program;
use App\Models\Question;
use App\Models\Sequence;
use App\Models\Shop;
use App\Models\Society;
use App\Models\Survey;
use App\Models\Theme;
use App\Models\User;
use Artisan;
use Illuminate\Support\Facades\DB;

class Export
{
	public function society() {
		$societies = DB::connection('old')->select('SELECT * FROM entite WHERE txt_nom IN (SELECT txt_nom FROM entite GROUP BY txt_nom HAVING COUNT(*) = 1) ORDER BY id_entite');
		foreach ($societies as $key => $val) {
			echo "society : \033[01;31m" . $val['id_entite'] . "[" .  $val['dat_crea'] . "]" . "\033[0m \n";
			$society = new Society();

			$society->id = $val['id_entite'];
			$society->name = $val['txt_nom'];
			$society->street = $val['txt_rue'];
			$society->postal_code = $val['txt_cp'];
			$society->city = $val['txt_ville'];
			$society->created_at = $val['dat_crea'] !== "-infinity" ? $val['dat_crea'] : date('Y-m-d');
			$society->created_by = 0; // txt_autcrea ??
			$society->parent_id = $val['id_ent_parent'];
			$society->code_totem = $val['cod_totem'];
			$society->logo_pdf = $val['logourl'];
			$society->show_percent = $val['showpercent'];
			$society->lat = $val['lat'];
			$society->lon = $val['lon'];
			//source_id ?

			$society->save();
		}
	}

	public function civility() {
		$civilities = DB::connection('old')->select('SELECT * FROM civilite');
		foreach ($civilities as $key => $val) {
			echo "\033[01;31m civility \033[0m \n";
			$civility = new Civility();

			$civility->id = $val['id_civilite'];
			$civility->name = $val['txt_civilite'];

			$civility->save();
		}
	}

	public function country() {
		$countries = DB::connection('old')->select('SELECT * FROM pays');
		foreach ($countries as $key => $val) {
			echo "\033[01;31m country \033[0m \n";
			$country = new Country();

			$country->id = $val['id_pays'];
			$country->name = $val['txt_pays'];
			$country->iso = "";

			$country->save();
		}
	}

	public function language() {
		$langs = DB::connection('old')->select('SELECT * FROM langues');

		foreach ($langs as $key => $val) {
			echo "\033[01;31m language \033[0m \n";
			$lang = new Language();

			$lang->id = $val['id_langue'];
			$lang->code = $val['cod_culture'];

			$lang->save();
		}
	}

	public function user() {
		// DB::disconnect('old');
		// DB::reconnect('old');
		$users = DB::connection('old')->select('SELECT * FROM "util" ORDER BY id_util');
		//$users = DB::connection('test')->getPdo();
        foreach ($users as $key => $val) {
        	echo "\033[01;31m user[" . $val['id_util'] . "] \033[0m \n";
        	$newUser = new User();

        	$newUser->id = $val['id_util'];
        	$newUser->country_id = $val['id_pays'];
        	$newUser->civility_id = $val['id_civilite'];
        	$newUser->society_id = $val['id_entite'];
        	$newUser->first_name = $val['txt_nom'] !== "" ? $val['txt_nom'] : "Complete";
        	$newUser->last_name = $val['txt_prenom'] !== "" ? $val['txt_prenom'] : "your name!";
        	$newUser->birth_date = $val['dat_naiss'];
        	$newUser->street = $val['txt_rue'];
        	$newUser->postal_code = $val['txt_cp'];
        	$newUser->city = $val['txt_ville'];
        	if (!filter_var($val['txt_login'], FILTER_VALIDATE_EMAIL)) {
        		continue;
        	} elseif (substr($val['txt_login'], -1) === "_") {
        		$email = substr($val['txt_login'], 0, strlen($val['txt_login']) - 2);
        		$email = str_replace("é", "e", $email);
        		$email = str_replace("è", "e", $email);
        		$email = str_replace("à", "a", $email);
        	} else {
        		$email = str_replace("é", "e", $val['txt_login']);
        		$email = str_replace("è", "e", $email);
        		$email = str_replace("à", "a", $email);
          	}
        	$newUser->email = $email;
        	$passwd = strlen($val['txt_mdp']) >= 6 ? $val['txt_mdp'] : "123456789";
        	if (strpos($passwd, "ç")) {
        		$passwd = "123456789";
        	}
        	$newUser->password = $passwd;
        	$newUser->parent_id = $val['id_parain'];
        	$newUser->scoring = $val['scoring'] !== null ? $val['scoring'] : 0;
        	$newUser->language_id = $val['id_lang'] !== null ? $val['id_lang'] : 2;
        	$newUser->sleepstatus = $val['sleepstatus'] !== null ? $val['sleepstatus'] : 0;
        	$newUser->commentuser = $val['commentuser'];
        	$newUser->commentsleep = $val['commentsleep'];
        	$newUser->lat = $val['lat'];
        	$newUser->lon = $val['lon'];
        	$newUser->registration_date = $val['registrationdate'];
        	$newUser->userActivity->user_level_id = $val['userlevelid'];
        	$newUser->issmicer = $val['issmicer'];
        	$newUser->secret_key = "k9kJsnZo1epdZDMe9vfz6VjjWl6QNAixAhruCnYH83C47LYwqHKllizBGyw7";

        	$newUser->save();
        }
	}

	public function seedRoleAndPermissions() {
		Artisan::call('db:seed', ['--class' => 'RoleSeeder']);
		Artisan::call('db:seed', ['--class' => 'SpecificPermissionSeeder']);
		Artisan::call('db:seed', ['--class' => 'PermissionSeeder']);
	}

	public function programs() {
		$programs = DB::connection('old')->select("SELECT * FROM programmes");
		$count = 0;
		foreach ($programs as $key => $val) {
			echo "\033[01;31m programs[" . $val['id_programme'] . ']' . "\033[0m \n";
			$program = new Program();

			$program->id = $val['id_programme'];
			$program->id_product = $val['id_product'];
			$program->society_id = 10934;

			if ($val['title'] !== null && $val['title'] !== '') {
				$program->name = $val['title'];
			} else {
				$program->name = "Program " . $count;
			}

			if ($val['txt_programme'] !== '' && $val['txt_programme'] !== null) {
				$program->text = $val['txt_programme'];
			} else {
				$program->text = "Texte de programme";
			}

			$program->info = $val['info'];

			$program->save();
			$count++;

			if ($val['id_client'] !== null && $val['id_client'] !== "") {
				if (User::where('id', $val['id_client'])->count() > 0) {
					DB::table('program_user')->insert([
						[
							'user_id' => $val['id_client'],
							'program_id' => $val['id_programme']
						]
					]);
				}
			}
		}
	}

	public function shops() {
		$count = 0;
		$shops = DB::connection('old')->select("SELECT * FROM pdvinfo");
		foreach ($shops as $key => $val) {
			if (Society::where('id', $val['identite'])->first()) {
				echo "\033[01;31m shop[" . $count . "] \033[0m \n";
				$shop = new Shop();

				$shop->language_id = $val['idlanguage'];
				$shop->phones = $val['phones'];
				$shop->work_days = $val['workdays'];
				$shop->work_hours = $val['workhours'];
				$shop->info = $val['info'];

				if (Shop::where('name', Society::where('id', $val['identite'])->first()->name)->first()) {
					$shop->name = Society::where('id', $val['identite'])->first()->name . " ";
				} else {
					$shop->name = Society::where('id', $val['identite'])->first()->name;
				}
				$shop->save();

				DB::table('shop_society')->insert([
					[
						'society_id' => $val['identite'],
						'shop_id' => $shop->id
					]
				]);
				$count++;
			} else {
				continue;
			}
		}
	}

	public function axes() {
		$axes = DB::connection('old')->select('SELECT * FROM axes ORDER BY id ASC');
		foreach ($axes as $key => $val) {
			if (Axe::where('name', $val['name'])->first() === null) {
				echo "\033[01;31m axe[" . $val['id'] . "] \033[0m \n";
				$axe = new Axe();

				$axe->id = $val['id'];
				$axe->society_id = 10934;
				$axe->name = $val['name'];
				//$axe->created_at = $val['created'];

				$axe->save();
			}
			// loop for shop_axe (i didn't find the old link table);
		}
	}

	public function sequence() {
		$seqs = DB::connection('old')->select('SELECT * FROM seq ORDER BY id_seq ASC');
		foreach ($seqs as $key => $val) {
			echo "Sequence[" . $val['id_seq'] . "]\n";
			$sequence = new Sequence();

			$sequence->id = $val['id_seq'];
			$sequence->library = false;
			$sequence->names = [["code" => "fr", "name" => $val['txt_critb']]];
			$sequence->society_id = 10934;
			$sequence->save();
		}
	}

	public function scenario() {
		//I didn't find the correct table in the old database

	}

	public function questionnaire() {
		$surveys = DB::connection('old')->select('SELECT * FROM quest ORDER BY id_quest ASC');
		foreach ($surveys as $key => $val) {
			$survey = new Survey();

			$survey->id = $val['id_quest'];
			$survey->names = $val['txt_quest'];
			$survey->society_id = 10934;

			$survey->save();
		}
	}

	public function theme() {
		$th = DB::connection('old')->select('SELECT * FROM theme ORDER BY id_theme ASC');
		foreach ($th as $key => $val) {
			echo "Theme[" . $val['id_theme'] . "]\n";
			$theme = new Theme();

			$theme->id = $val['id_theme'];
			$theme->name = $val['txt_theme'];
			$theme->society_id = 10934;

			$theme->save();
		}
	}

	public function criteriaA() {
		$th = DB::connection('old')->select('SELECT * FROM crita ORDER BY id_crita ASC');
		foreach ($th as $key => $val) {
			if (CriteriaA::where('name', $val['txt_crita'])->first() === null) {
				echo "Criteria A[" . $val['id_crita'] . "]\n";
				$cria = new CriteriaA();

				$cria->id = $val['id_crita'];
				$cria->name = $val['txt_crita'];
				$cria->society_id = 10934;

				$cria->save();
			} else {
				continue;
			}
		}
	}

	public function criteriaB() {
		$th = DB::connection('old')->select('SELECT * FROM critb ORDER BY id_critb ASC');
		foreach ($th as $key => $val) {
			if (CriteriaB::where('name', $val['txt_critb'])->first() === null) {
				echo "Criteria B[" . $val['id_critb'] . "]\n";
				$crib = new CriteriaB();

				$crib->id = $val['id_critb'];
				$crib->name = $val['txt_critb'];
				$crib->society_id = 10934;

				$crib->save();
			} else {
				continue;
			}
		}
	}

	public function jobs() {
		$th = DB::connection('old')->select('SELECT * FROM metier ORDER BY id_metier ASC');
		foreach ($th as $key => $val) {
			if (Job::where('name', $val['txt_critb'])->first() === null) {
				echo "Metier[" . $val['id_metier'] . "]\n";
				$j = new Job();

				$j->id = $val['id_metier'];
				$j->name = $val['txt_critb'];
				$j->society_id = 10934;

				$j->save();
			} else {
				continue;
			}
		}
	}

	public function questions() {
		$th = DB::connection('old')->select('SELECT * FROM question JOIN typ_question ON typ_question.id_typquestion = question.id_typquestion JOIN image ON question.id_image = image.id_image JOIN typ_rep ON typ_rep.id_typrep = question.id_typrep JOIN lnk_langquestion ON lnk_langquestion.id_question = question.id_question WHERE lnk_langquestion.id_langue = 1 ORDER BY question.id_question ASC');
		foreach ($th as $key => $val) {
			$quest = new Question();
			//die(var_dump($val));
			$quest->id = $val['id_question'];
			$quest->type = $val['txt_typquestion'] !== null ? $val['txt_typquestion'] : "";
			$quest->answer_max = 0;
			$quest->society_id=  10934;
			$quest->names = [
				[
					'name' => $val['txt_question'],
					'code' => 'fr'
				]
			];
			$quest->image = $val['txt_url'] !== ""? $val['txt_url'] : null;

			$quest->answer_min = ''; //correspondance avec anciens types
			$numb_mini_ans = null;
			switch ($val['id_typrep']) {
				case 1:
					$numb_mini_ans = 2;
					break;
				case 2:
					$numb_mini_ans = 1;
					break;
				case 3:
					$numb_mini_ans = 3;
					break;
				case 4:
					$numb_mini_ans = 3;
					break;
				case 5:
					$numb_mini_ans = 0;
					break;
				case 6:
					$numb_mini_ans = 0;
					break;
				case 7:
					$numb_mini_ans = 2;
					break;
				case 8:
					$numb_mini_ans = 1;
					break;
				case 9:
					$numb_mini_ans = 1;
					break;
				default:
					$numb_mini_ans = 0;
					break;
			}
			$quest->answer_min = $numb_mini_ans; //correspondance avec anciens types

			$quest->save();
		}
	}

	public function answers() {

	}
}
