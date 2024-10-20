<?php

namespace App\Form;

use App\Entity\Product;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'attr' => ['class' => 'form-control', 'placeholder' => 'Enter product name'],
                'label' => 'Product Name',
            ])
            ->add('description', TextareaType::class, [
                'attr' => ['class' => 'form-control', 'placeholder' => 'Enter product description'],
                'label' => 'Description',
            ])
            ->add('price', NumberType::class, [
                'attr' => ['class' => 'form-control', 'placeholder' => 'Enter product price'],
                'label' => 'Price',
                'scale' => 2, // Ensures two decimal places for price
            ])
            ->add('stockQuantity', NumberType::class, [
                'attr' => ['class' => 'form-control', 'placeholder' => 'Enter stock quantity'],
                'label' => 'Stock Quantity',
                'scale' => 0, // Ensures whole numbers for stock quantity
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'product_item',
        ]);
    }
}
