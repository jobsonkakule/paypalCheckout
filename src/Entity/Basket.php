<?php

namespace App\Entity;

// use App\Repository\BasketRepository;
use Doctrine\ORM\Mapping as ORM;

// /**
//  * @ORM\Entity(repositoryClass=BasketRepository::class)
//  */
class Basket
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    private $products;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProducts()
    {
        return $this->products;
    }
    
    /**
     * setProducts
     *
     * @param  mixed $products
     * @return Basket
     */
    public function setProducts($products)
    {
        $this->products = $products;
        return $this;
    }
    
    public function addProduct(Product $product) {
        $this->products[] = $product;
        return $this;
    }

    public function getPrice(): ?float
    {
        return array_reduce($this->getProducts(), function ($total, Product $product) {
            return $product->getPrice() + $total;
        }, 0);
    }

    public function getVatPrice($rate): ?float {
        return round($this->getPrice() * $rate * 100) / 100;
    }

    public static function fake() {
        $products = array_map(function ($price) {
            return (new Product())
                ->setPrice($price)
                ->setName('Produit ' . $price);
        }, [1.21, 10.22, 35.70]);
        return (new self())
            ->setProducts($products);
    }
}
