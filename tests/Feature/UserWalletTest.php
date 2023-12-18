<?php

namespace Tests\Feature;

use App\Enums\Currency;
use App\Exceptions\NotFoundWalletException;
use App\Models\EuroWallet;
use App\Models\RubWallet;
use App\Models\UsdWallet;
use App\Models\UserWallet;
use App\Repository\UserWalletRepository;
use App\Сurrencies\Euro;
use App\Сurrencies\Rub;
use App\Сurrencies\Usd;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class UserWalletTest extends TestCase
{
    use RefreshDatabase;

    public int $user_id;

    private array $walletTables = ['rub_wallet', 'euro_wallet', 'usd_wallet'];

    public function setUp(): void
    {
        parent::setUp();

        $this->user_id = 42;
    }

    public function test_create_new_user_wallet()
    {
        $this->post(route('create-wallet'), [
            'user_id' => $this->user_id,
            'default_currency' => Currency::RUB->value
        ]);

        $this->assertDatabaseHas('user_wallet', [
            'user_id' => $this->user_id,
            'default_currency' => Currency::RUB->value
        ]);
    }

    public function test_create_new_currency_wallet()
    {
        $wallet = UserWallet::factory(['user_id' => $this->user_id])->create();

        foreach(Currency::cases() as $case) {
            $this->post(route('create-currency-wallet'), [
                'wallet_id' => $wallet->id,
                'currency' => $case->value
            ]);
        }

        foreach ($this->walletTables as $table) {
            $this->assertDatabaseHas($table, [
                'wallet_id' => $wallet->id,
            ]);
        }
    }

    public function test_create_new_currency_wallet_if_wallet_not_found()
    {
        $response = $this->post(route('create-currency-wallet'), [
            'wallet_id' => 42,
            'currency' => Currency::RUB->value
        ]);

        $response->assertJson(["error" => "Wallet not found"]);
    }

    public function test_create_new_currency_wallet_if_wallet_not_found_and_wait_exception()
    {
        $this->expectException(NotFoundWalletException::class);

        $rub = $this->createMock(Rub::class);
        $usd = $this->createMock(Usd::class);
        $euro = $this->createMock(Euro::class);

        $request = new Request();
        $request->merge(['wallet_id' => 123, 'currency' => 'rub']); // Здесь вы можете задать несуществующий ID кошелька

        $walletController = new UserWalletRepository($rub, $euro, $usd);
        $walletController->newCurrencyWallet(100, 'rub');
    }

    public function test_get_total_balance_by_default_currency()
    {
        $wallet = UserWallet::factory(['user_id' => $this->user_id])->create();

        RubWallet::factory(['wallet_id' => $wallet->id])->create();

        EuroWallet::factory(['wallet_id' => $wallet->id])->create();

        UsdWallet::factory(['wallet_id' => $wallet->id])->create();

        $response = $this->get(route('get-balance', ['user_id' => $wallet->user_id]));

        $this->assertEquals(25100, $response->json());
    }

    public function test_get_total_balance_by_usd()
    {
        $wallet = UserWallet::factory(['user_id' => $this->user_id])->create();

        RubWallet::factory(['wallet_id' => $wallet->id, 'sum' => 10000])->create();

        EuroWallet::factory(['wallet_id' => $wallet->id, 'sum' => 100])->create();

        UsdWallet::factory(['wallet_id' => $wallet->id, 'sum' => 100])->create();

        $response = $this->get(route('get-balance', [
            'user_id' => $wallet->user_id,
            'currency' => Currency::USD->value
        ]));

        $this->assertEquals(350, $response->json());
    }

    public function test_get_total_balance_in_rub_and_euro_without_usd()
    {
        $wallet = UserWallet::factory(['user_id' => $this->user_id])->create();

        RubWallet::factory(['wallet_id' => $wallet->id, 'sum' => 10000])->create();

        EuroWallet::factory(['wallet_id' => $wallet->id, 'sum' => 100])->create();

        $response = $this->get(route('get-balance', [
            'user_id' => $wallet->user_id,
            'currency' => Currency::USD->value
        ]));

        $this->assertEquals(250, $response->json());
    }

    public function test_add_money_to_wallet()
    {
        $wallet = UserWallet::factory(['user_id' => $this->user_id])->create();

        RubWallet::factory(['wallet_id' => $wallet->id, 'sum' => 0])->create();

        EuroWallet::factory(['wallet_id' => $wallet->id, 'sum' => 0])->create();

        UsdWallet::factory(['wallet_id' => $wallet->id, 'sum' => 0])->create();

        foreach(Currency::cases() as $case) {
            $this->post(route('add-money'), [
                'wallet_id' => $wallet->id,
                'currency' => $case->value,
                'sum' => 100
            ]);
        }

        foreach ($this->walletTables as $table) {
            $this->assertDatabaseHas($table, [
                'wallet_id' => $wallet->id,
                'sum' => 100
            ]);
        }
    }

    public function test_write_off_money_from_wallet()
    {
        $wallet = UserWallet::factory(['user_id' => $this->user_id])->create();

        RubWallet::factory(['wallet_id' => $wallet->id])->create();

        EuroWallet::factory(['wallet_id' => $wallet->id])->create();

        UsdWallet::factory(['wallet_id' => $wallet->id])->create();

        foreach(Currency::cases() as $case) {
            $this->post(route('write-off-money'), [
                'wallet_id' => $wallet->id,
                'currency' => $case->value,
                'sum' => 99
            ]);
        }

        foreach ($this->walletTables as $table) {
            $this->assertDatabaseHas($table, [
                'wallet_id' => $wallet->id,
                'sum' => 1
            ]);
        }
    }

    public function test_write_off_money_from_wallet_if_there_is_not_enough_money()
    {
        $wallet = UserWallet::factory(['user_id' => $this->user_id])->create();

        RubWallet::factory(['wallet_id' => $wallet->id])->create();

        $response = $this->post(route('write-off-money'), [
            'wallet_id' => $wallet->id,
            'currency' => Currency::RUB->value,
            'sum' => 1000
        ]);

        $response->assertJson(["error" => "На вашем рублевом счете не достаточно денег"]);
    }

    public function test_change_wallet_default_currency()
    {
        UserWallet::factory(['user_id' => $this->user_id])->create();

        $this->post(route('change-currency'), [
            'user_id' => $this->user_id,
            'currency' => Currency::USD->value,
        ]);

        $this->assertDatabaseHas('user_wallet', [
            'user_id' => $this->user_id,
            'default_currency' => Currency::USD->value
        ]);
    }


}
