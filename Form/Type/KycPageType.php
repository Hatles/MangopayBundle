<?php
/**
 * Created by PhpStorm.
 * User: kifkif
 * Date: 21/07/2017
 * Time: 18:07
 */

namespace Troopers\MangopayBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class KycPageType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('file', FileType::class);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Troopers\MangopayBundle\Entity\KycPage'
        ));
    }

    public function getBlockPrefix()
    {
        return 'kyc_page_form';
    }
}