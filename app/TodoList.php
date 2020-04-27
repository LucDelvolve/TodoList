<?php

namespace App;

use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Model;

class TodoList extends Model
{
    protected $fillable = [
        'name', 'description'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(Item::class);
    }

    /**
     * @param Item $item
     * @return Item
     * @throws Exception
     */
    public function canAddItem(Item $item): Item
    {
        if (is_null($item) || !$item->isValid()) {
            throw new Exception('Item is null or invalid');
        }

        // dd($this->user());

        if (is_null($this->user()) || !$this->user()->isValid()) {
            throw new Exception('User is null or invalid');
        }

        // dd($this->items());

        if ($this->actualItemsCount() >= 10) {
            throw new Exception('Todo list has too many items');
        }

        $lastItem = $this->getLastItem();
        if (!is_null($this->getLastItem()) && Carbon::now()->subMinutes(30)->isBefore($lastItem->created_at)) {
            throw new Exception('Last item is too recent');
        }

        return $item;
    }

    public function getLastItem(): ?Item
    {
        return $this->items()->orderBy('created_at', 'desc')->firstOr(null);
    }

    protected function actualItemsCount()
    {
        return sizeof($this->items()->get());
    }

}
