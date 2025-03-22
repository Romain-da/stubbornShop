<?php

namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ProductFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $products = [
            [
                'name' => 'Blackbelt',
                'description' => 'Un super sweat',
                'price' => 29.90,
                'image' => '1-67d6a35c217db.jpg',
                'is_featured' => true,
            ],
            [
                'name' => 'BlueBelt',
                'description' => 'Un super sweat',
                'price' => 29.90,
                'image' => '2-67cf24b5d4f6c.jpg',
                'is_featured' => false,
            ],
            [
                'name' => 'Street',
                'description' => 'Un super sweat',
                'price' => 34.50,
                'image' => '3-67d6a795b484f.jpg',
                'is_featured' => false,
            ],
            [
                'name' => 'Pokeball',
                'description' => 'Un super sweat',
                'price' => 45,
                'image' => '4-67d6a7add56d3.jpg',
                'is_featured' => true,
            ],
            [
                'name' => 'PinkLady',
                'description' => 'Un super sweat',
                'price' => 29.90,
                'image' => '5-67d6a7c605164.jpg',
                'is_featured' => false,
            ],
            [
                'name' => 'Snow',
                'description' => 'Un super sweat',
                'price' => 32,
                'image' => '6-67d6a7dace3fa.jpg',
                'is_featured' => false,
            ],[
                'name' => 'Greyback',
                'description' => 'Un super sweat',
                'price' => 28.50,
                'image' => '7-67d6a7eff21a4.jpg',
                'is_featured' => false,
            ],
            [
                'name' => 'BlueCloud',
                'description' => 'Un super sweat',
                'price' => 45,
                'image' => '8-67d6a8011fd51.jpg',
                'is_featured' => false,
            ],
            [
                'name' => 'BornInUsa',
                'description' => 'Un super sweat',
                'price' => 59.90,
                'image' => '9-67d6a81bdc414.jpg',
                'is_featured' => true,
            ],
            [
                'name' => 'GreenSchool',
                'description' => 'Un super sweat',
                'price' => 42.20,
                'image' => '10-67d6a832c5351.jpg',
                'is_featured' => false,
            ],
        ];

        foreach ($products as $data) {
            $product = new Product();
            $product->setName($data['name']);
            $product->setDescription($data['description']);
            $product->setPrice($data['price']);
            $product->setImage($data['image']);
            $product->setIsFeatured($data['is_featured']);
            $product->setCreatedAt(new \DateTimeImmutable());

            $manager->persist($product);
        }

        $manager->flush();
    }
}
