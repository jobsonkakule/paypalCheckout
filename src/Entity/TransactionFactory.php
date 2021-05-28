<?php
namespace App\Entity;

use PayPal\Api\Item;
use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\ItemList;
use PayPal\Api\Transaction;

class TransactionFactory {

    public static function fromBasket(Basket $basket, float $vatRate = 0): Transaction
    {
        
        $list = new ItemList();
        foreach ($basket->getProducts() as $product) {
            $item = (new Item())
                ->setName($product->getName())
                ->setPrice($product->getPrice())
                ->setCurrency('USD')
                ->setQuantity(1);
            $list->addItem($item);
        }

        $details = (new Details())
            ->setTax($basket->getVatPrice($vatRate))
            ->setSubtotal($basket->getPrice());

        $amount = (new Amount())
            ->setTotal($basket->getPrice() + $basket->getVatPrice($vatRate))
            ->setCurrency("USD")
            ->setDetails($details);
        return (new Transaction())
            ->setItemList($list)
            ->setDescription("Achat sur monsite.cd ")
            ->setAmount($amount)
            ->setCustom('demo-1');
    }
}