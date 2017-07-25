<?php
/**
 * Created by PhpStorm.
 * User: kifkif
 * Date: 21/07/2017
 * Time: 18:07
 */

namespace Troopers\MangopayBundle\Form\Type;


use MLC\CoreBundle\Form\Type\CollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class KycDocumentType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('pages', CollectionType::class, array(
            'allow_add' => true,
            'allow_delete' => false,
            'entry_type' => KycPageType::class,
            'add_text' => 'New Page',
            'label' => false,
            'entry_options' => array(
                'attr' => array(
                    'class' => 'uploadFile'
                )
            )
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Troopers\MangopayBundle\Entity\KycDocument'
        ));
    }

    public function getBlockPrefix()
    {
        return 'kyc_page_form';
    }
}