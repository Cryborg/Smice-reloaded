<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public string $email = 'new_user@smice.com';
    public string $correctPassword = 'password';
    public string $incorrectPassword = 'this_is_so_wrong!';

    private function createUser()
    {
        $this->actingAs(User::factory()->create([
            'email' => $this->email,
            'password' => Hash::make($this->correctPassword)
        ]));
    }

    /**
     * Login
     *
     * @dataProvider getCredentials
     * @param $email
     * @param $password
     * @param $httpResponse
     *
     * @return void
     */
    public function testLoginWithVariousCredentials($email, $password, $httpResponse): void
    {
        $this->createUser();

        $this
            ->postJson(route('auth.login'), [
                'email' => $email,
                'password' => $password
            ])
            ->assertStatus($httpResponse);
    }

    /**
     * Logout
     *
     * @return void
     */
    public function testLogout(): void
    {
        $this->createUser();

        // Login
        $this
            ->postJson(route('auth.login'), [
                'email' => $this->email,
                'password' => $this->correctPassword
            ])
            ->assertStatus(Response::HTTP_OK);

        // Logout
        $this
            ->postJson(route('auth.logout'))
            ->assertStatus(Response::HTTP_OK);

        // Try to access a protected route
        $this
            ->getJson('/user')
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Register
     *
     * @dataProvider getAccountsToRegister
     *
     * @param $name
     * @param $email
     * @param $password
     * @param $httpResponse
     *
     * @return void
     */
    public function testRegisterANewUser($name, $email, $password, $httpResponse): void
    {
        $this
            ->postJson(route('auth.register'), [
                'name' => $name,
                'email' => $email,
                'password' => $password
            ])
            ->assertStatus($httpResponse);

        $this->assertTrue(true);
    }

    /**
     * Refresh
     *
     * @return void
     */
    public function testRefreshAUserToken(): void
    {
        $this->createUser();

        // Login
        $this
            ->postJson(route('auth.login'), [
                'email' => $this->email,
                'password' => $this->correctPassword
            ])
            ->assertStatus(Response::HTTP_OK);

        // Refresh the token
        $this
            ->postJson(route('auth.refresh'))
            ->assertStatus(Response::HTTP_OK);

        // Try to access a protected route
        $this
            ->getJson('/user')
            ->assertStatus(Response::HTTP_OK);
    }

    /**
     * Data provider to test Login feature
     *
     * @return array[]
     */
    public function getCredentials(): array
    {
        return [
            'Correct credentials' => [
                'email' => $this->email,
                'password' => $this->correctPassword,
                Response::HTTP_OK
            ],
            'Incorrect credentials' => [
                'email' => $this->email,
                'password' => $this->incorrectPassword,
                Response::HTTP_UNAUTHORIZED
            ],
        ];
    }

    /**
     * Data provider to test Register feature
     *
     * @return array[]
     */
    public function getAccountsToRegister(): array
    {
        return [
            'Correct credentials' => [
                'name' => 'Fake name',
                'email' => $this->email,
                'password' => $this->correctPassword,
                Response::HTTP_OK
            ],
            'Incorrect credentials' => [
                'name' => 'Fake name 2',
                'email' => 'this_is_not_an_email!',
                'password' => $this->incorrectPassword,
                Response::HTTP_UNPROCESSABLE_ENTITY
            ],
        ];
    }
}
