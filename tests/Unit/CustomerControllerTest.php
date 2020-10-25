<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

use App\Http\Controllers\CustomerController;
use App\Models\Customers;

class CustomerControllerTest extends TestCase
{
    protected static $customerIDs = [];

    public function testStore()
    {
        // 打HTTP Post，去request CustomerController store method (儲存一筆資料)
        $response = $this->postJson(action([CustomerController::class, 'store']), [
            'name' => 'name-A',
            'phone' => '0911123456'
        ]);

        // 確認store method是return 302 redirect無誤
        $response->assertStatus(302);

        // 取最新insert的customer id
        // 因為最新的customer id被store method帶在redirect url中，所以這裡從url中拆解出來
        $redirectUrlExploded = explode('/', $response->headers->get('location'));
        $customerID = array_pop($redirectUrlExploded);

        // 確認customer id都是數字無誤
        $this->assertStringMatchesFormat('%d', $customerID);

        // 確認insert的資料內容無誤
        $customer = Customers::find($customerID);
        $this->assertSame('name-A', $customer->name);
        $this->assertSame('0911123456', $customer->phone);

        // 將customer id存到 self::$customerIDs 中，後續的test會需要
        self::$customerIDs[] = $customerID;
    }

    /**
     * @depends testStore
     */
    public function testShow()
    {
        // 從 self::$customerIDs 取回customer id
        // 因為測試動作是有連貫性而非獨立的，所以customer id才需要互相傳遞
        $customerID = self::$customerIDs[0];

        // 打HTTP Get，去request CustomerController show method (顯示一筆資料)
        $response = $this->get(action([CustomerController::class, 'show'], ['id' => $customerID]));

        // 確認HTTP status 200無誤
        $response->assertOk();

        // 確認view為"customer"無誤
        $response->assertViewIs('customer');

        // 確認會傳遞$customer給view，而其內容應該就是要等於剛剛 testStore() 中儲存的無誤
        $customer = Customers::find($customerID);
        $response->assertViewHas('customer', $customer);
    }

    /**
     * @depends testShow
     */
    public function testDestroy()
    {
        $customerID = self::$customerIDs[0];

        // 打HTTP Get(Delete)，去request CustomerController destroy method (刪除一筆資料)
        $response = $this->deleteJson(action([CustomerController::class, 'destroy'], ['id' => $customerID]));

        // 確認HTTP status 200無誤
        $response->assertOk();

        // 確認view為"welcome"無誤
        $response->assertViewIs('welcome');

        // 確認此筆資料確實已被刪除無誤
        $customer = Customers::find($customerID);
        $this->assertNull($customer);
    }
}