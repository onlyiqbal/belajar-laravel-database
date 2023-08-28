<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RawQueryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::delete('DELETE FROM categories');
    }

    public function testCRUD()
    {
        DB::insert('INSERT INTO categories(id, name, description, created_at) VALUES (?,?,?,?)', [
            'GADGET',
            'Gadget',
            'Gadget Category',
            '2023-08-22 20:10:10'
        ]);

        $result = DB::select('SELECT * FROM categories WHERE id = ?', ['GADGET']);

        $this->assertCount(1, $result);
        $this->assertEquals('GADGET', $result[0]->id);
        $this->assertEquals('Gadget', $result[0]->name);
        $this->assertEquals('Gadget Category', $result[0]->description);
        $this->assertEquals('2023-08-22 20:10:10', $result[0]->created_at);
    }

    public function testCRUDNamedParameter()
    {
        DB::insert(
            'INSERT INTO categories(id, name, description, created_at) VALUES (:id,:name,:description,:created_at)',
            [
                'id' => 'GADGET',
                'name' => 'Gadget',
                'description' => 'Gadget Category',
                'created_at' => '2023-08-22 20:10:10'
            ]
        );

        $result = DB::select('SELECT * FROM categories WHERE id = ?', ['GADGET']);

        $this->assertCount(1, $result);
        $this->assertEquals('GADGET', $result[0]->id);
        $this->assertEquals('Gadget', $result[0]->name);
        $this->assertEquals('Gadget Category', $result[0]->description);
        $this->assertEquals('2023-08-22 20:10:10', $result[0]->created_at);
    }
}