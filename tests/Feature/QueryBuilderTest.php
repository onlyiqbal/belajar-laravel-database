<?php

namespace Tests\Feature;

use Database\Seeders\CategorySeeder;
use Database\Seeders\CounterSeeder;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class QueryBuilderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::delete('DELETE FROM products');
        DB::delete('DELETE FROM categories');
        DB::delete('DELETE FROM counters');
    }

    public function testInsert()
    {
        DB::table('categories')->insert([
            'id' => 'GADGET',
            'name' => 'Gadget',
            'description' => 'Gadget Category',
            'created_at' => '2023-08-22 21:46:10'
        ]);

        DB::table('categories')->insert([
            'id' => 'FOOD',
            'name' => 'Food',
            'description' => 'Food Category',
            'created_at' => '2023-08-22 21:46:10'
        ]);

        $result = DB::select('SELECT COUNT(id) as total FROM categories');
        $this->assertEquals(2, $result[0]->total);
    }

    public function testSelect()
    {
        $this->testInsert();

        $collection = DB::table('categories')->select(['id', 'name'])->get();

        $this->assertNotNull($collection);

        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function insertCategories()
    {
        $this->seed(CategorySeeder::class);
    }

    public function testWhere()
    {
        $this->insertCategories();

        $collection = DB::table('categories')->orWhere(function (Builder $builder) {
            $builder->where('id', '=', 'LAPTOP');
            $builder->orWhere('id', '=', 'FOOD');
            // SELECT * FORM categories WHERE (id = 'LAPTOP' OR id = 'FOOD') 
        })->get();

        $this->assertCount(2, $collection);
        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testWhereBetween()
    {
        $this->insertCategories();

        $collection = DB::table('categories')->whereBetween('created_at', [
            '2023-08-23 20:02:10',
            '2023-09-23 20:02:10'
        ])->get();

        $this->assertCount(4, $collection);
        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testWhereIn()
    {
        $this->insertCategories();

        $collection = DB::table('categories')->whereIn('id', [
            'FOOD',
            'LAPTOP'
        ])->get();

        $this->assertCount(2, $collection);
        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testWhereNull()
    {
        $this->insertCategories();

        $collection = DB::table('categories')->whereNull('description')->get();

        $this->assertCount(4, $collection);
        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testWhereDate()
    {
        $this->insertCategories();

        $collection = DB::table('categories')->whereDate('created_at', '2023-08-23')->get();

        $this->assertCount(4, $collection);
        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testUpdate()
    {
        $this->insertCategories();

        DB::table('categories')->where('id', '=', 'FOOD')->update([
            'name' => 'Sepatu'
        ]);

        $collection = DB::table('categories')->where('name', '=', 'Sepatu')->get();

        $this->assertCount(1, $collection);
        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testUpsert()
    {
        $this->insertCategories();

        DB::table('categories')->updateOrInsert([
            'id' => 'VOUCHER'
        ], [
            'id' => 'VOUCHER',
            'name' => 'Voucher',
            'created_at' => '2023-09-23 20:02:10'
        ]);

        $collection = DB::table('categories')->where('id', '=', 'VOUCHER')->get();

        $this->assertCount(1, $collection);
        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testIncrement()
    {
        $this->seed(CounterSeeder::class);

        DB::table('counters')->where('id', '=', 'sample')->increment('counter', 1);

        $collection = DB::table('counters')->where('id', '=', 'sample')->get();
        $this->assertCount(1, $collection);
        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testDelete()
    {
        $this->insertCategories();
        DB::table('categories')->where('id', '=', 'FASHION')->delete();

        $collection = DB::table('categories')->where('id', '=', 'FASHION')->get();
        $this->assertCount(0, $collection);
    }

    public function insertProducts()
    {
        $this->insertCategories();

        DB::table('products')->insert([
            'id' => '1',
            'name' => 'iPhone 14 Pro Max',
            'category_id' => 'GADGET',
            'price' => 20000000
        ]);

        DB::table('products')->insert([
            'id' => '2',
            'name' => 'Samsung Galaxy S21',
            'category_id' => 'GADGET',
            'price' => 18000000
        ]);
    }

    public function testJoin()
    {
        $this->insertProducts();

        $collection = DB::table("products")
            ->join("categories", "products.category_id", '=', 'categories.id')
            ->select("products.id", "products.name", "products.price", "categories.name as category_name")
            ->get();

        self::assertCount(2, $collection);
        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testOrdering()
    {
        $this->insertProducts();

        $collection = DB::table('products')->whereNotNull('id')->orderBy('price', 'desc')
            ->orderBy('name', 'desc')->get();

        $this->assertCount(2, $collection);
        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testPaging()
    {
        $this->insertCategories();

        $collection = DB::table('categories')->skip(0)->take(2)->get();

        $this->assertCount(2, $collection);
        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function insertManyCategories()
    {
        for ($i = 1; $i <= 100; $i++) {
            DB::table('categories')->insert([
                'id' => "GADGET-$i",
                'name' => "Gadget $i",
                'created_at' => '2023-08-28 20:02:30'
            ]);
        }
    }

    public function testChunk()
    {
        $this->insertManyCategories();

        DB::table('categories')->orderBy('id')
            ->chunk(10, function ($categories) {
                $this->assertNotNull($categories);
                Log::info('Start Chunk');
                $categories->each(function ($category) {
                    Log::info(json_encode($category));
                });
                Log::info('End Chunk');
            });
    }

    public function testLazy()
    {
        $this->insertManyCategories();

        $collection = DB::table('categories')->orderBy('id')->lazy(10)->take(3);
        $this->assertNotNull($collection);

        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testCursor()
    {
        $this->insertManyCategories();

        $collection = DB::table('categories')->orderBy('id')->cursor();
        $this->assertNotNull($collection);

        $collection->each(function ($item) {
            Log::info(json_encode($item));
        });
    }

    public function testAggregate()
    {
        $this->insertProducts();

        $result = DB::table('products')->count('id');
        $this->assertEquals(2, $result);

        $result = DB::table('products')->min('price');
        $this->assertEquals(18000000, $result);

        $result = DB::table('products')->max('price');
        $this->assertEquals(20000000, $result);

        $result = DB::table('products')->avg('price');
        $this->assertEquals(19000000, $result);
    }

    public function testQueryBuilderRaw()
    {
        $this->insertProducts();

        $collection = DB::table('products')->select(
            DB::raw('count(id) as total_product'),
            DB::raw('max(price) as max_price'),
            DB::raw('min(price) as min_price'),
        )->get();

        $this->assertEquals(2, $collection[0]->total_product);
        $this->assertEquals(20000000, $collection[0]->max_price);
        $this->assertEquals(18000000, $collection[0]->min_price);
    }

    public function insertProductFood()
    {
        DB::table('products')->insert([
            'id' => '3',
            'name' => 'Bakso',
            'category_id' => 'FOOD',
            'price' => 15000
        ]);

        DB::table('products')->insert([
            'id' => '4',
            'name' => 'Mie Ayam',
            'category_id' => 'FOOD',
            'price' => 12000
        ]);
    }

    public function testGroupBy()
    {
        $this->insertProducts();
        $this->insertProductFood();

        $collection = DB::table('products')
            ->select('category_id', DB::raw('count(*) as total_product'))
            ->groupBy('category_id')
            ->orderBy('category_id', 'desc')
            ->get();

        $this->assertCount(2, $collection);
        $this->assertEquals('GADGET', $collection[0]->category_id);
        $this->assertEquals('FOOD', $collection[1]->category_id);
    }

    public function testGroupByHaving()
    {
        $this->insertProducts();
        $this->insertProductFood();

        $collection = DB::table('products')
            ->select('category_id', DB::raw('count(*) as total_product'))
            ->groupBy('category_id')
            ->having(DB::raw('count(*)', '>', 2))
            ->orderBy('category_id', 'desc')
            ->get();

        $this->assertCount(0, $collection);
    }

    public function testLocking()
    {
        $this->insertProducts();

        DB::transaction(function () {
            $collection = DB::table('products')
                ->where('id', '=', '1')
                ->lockForUpdate()
                ->get();

            $this->assertCount(1, $collection);
        });
    }

    public function testPaginate()
    {
        $this->insertCategories();

        $paginate = DB::table('categories')->paginate(2, '*', 'page', 1);

        $this->assertEquals(1, $paginate->currentPage());
        $this->assertEquals(2, $paginate->perPage());
        $this->assertEquals(2, $paginate->lastPage());
        $this->assertEquals(4, $paginate->total());

        $collection = $paginate->items();
        $this->assertCount(2, $collection);
        foreach ($collection as $item) {
            Log::info(json_encode($item));
        }
    }

    public function testIterasiPagination()
    {
        $this->insertCategories();

        $page = 1;
        while (true) {
            $paginate = DB::table('categories')->paginate(2, '*', 'page', $page);

            if ($paginate->isEmpty()) {
                break;
            } else {
                $page++;

                $collection = $paginate->items();
                $this->assertCount(2, $collection);
                foreach ($collection as $item) {
                    Log::info(json_encode($item));
                }
            }
        }
    }

    public function testCurosorPaginate()
    {
        $this->insertCategories();

        $cursor = 'id';
        while (true) {
            $paginate = DB::table('categories')->orderBy('id')->cursorPaginate(2, cursor: $cursor);

            foreach ($paginate->items() as $item) {
                $this->assertNotNull($item);
                Log::info(json_encode($item));
            }

            $cursor = $paginate->nextCursor();
            if ($cursor == null) {
                break;
            }
        }
    }
}