<?php

namespace Tests\Feature;

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::delete('DELETE FROM categories');
    }

    public function testTransactionSuccess()
    {
        DB::transaction(function () {
            DB::insert(
                'INSERT INTO categories(id, name, description, created_at) VALUES (?,?,?,?)',
                [
                    'GADGET',
                    'Gadget',
                    'Gadget Category',
                    '2023-08-22 20:10:10'
                ]
            );

            DB::insert('INSERT INTO categories(id, name, description, created_at) VALUES (?,?,?,?)', [
                'FOOD',
                'Food',
                'Food Category',
                '2023-08-22 20:10:10'
            ]);
        });

        $result = DB::select('SELECT * FROM categories');

        $this->assertCount(2, $result);
    }

    public function testTransactionFailed()
    {
        try {
            DB::transaction(function () {
                DB::insert('INSERT INTO categories(id, name, description, created_at) VALUES (?,?,?,?)', [
                    'GADGET',
                    'Gadget',
                    'Gadget Category',
                    '2023-08-22 20:10:10'
                ]);

                DB::insert('INSERT INTO categories(id, name, description, created_at) VALUES (?,?,?,?)', [
                    'GADGET',
                    'Food',
                    'Food Category',
                    '2023-08-22 20:10:10'
                ]);
            });
        } catch (QueryException $error) {
            //expected
        }

        $result = DB::select('SELECT * FROM categories');

        $this->assertCount(0, $result);
    }

    public function testManualTransactionSuccess()
    {
        try {
            DB::beginTransaction();
            DB::transaction(function () {
                DB::insert('INSERT INTO categories(id, name, description, created_at) VALUES (?,?,?,?)', [
                    'GADGET',
                    'Gadget',
                    'Gadget Category',
                    '2023-08-22 20:10:10'
                ]);

                DB::insert('INSERT INTO categories(id, name, description, created_at) VALUES (?,?,?,?)', [
                    'FOOD',
                    'Food',
                    'Food Category',
                    '2023-08-22 20:10:10'
                ]);
                DB::commit();
            });
        } catch (QueryException $error) {
            DB::rollBack();
        }

        $result = DB::select('SELECT * FROM categories');

        $this->assertCount(2, $result);
    }
}