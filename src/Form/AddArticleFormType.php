<?php

namespace App\Form;

use App\Entity\Article;
use App\Entity\Tags;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class AddArticleFormType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {


        $builder
            ->add('title')
            ->add('content', CKEditorType::class)
            ->add('imageFileName', FileType::class, [
                'mapped' => false,
                'required' => false,
            ])

            ->add('tags', EntityType::class, [
            'class' => Tags::class,
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('t')
                    ->andwhere('t.isMainTag = true');
            },
            'choice_label' => 'Name',
            'mapped' => true,
            'multiple' => true,
            'expanded' => true,
            ]);

        /** @var Article|null $article */
        $article = $options['data'] ?? null;
        $tag = $article ? $article->getTags()->current()->getName() : null;

        $builder->get('tags')->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) use($tag) {
                $form = $event->getForm();
                $this->setupSpecificCategoryTag(
                    $form->getParent(),
                    $tag
                );
            });

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($tag) {
                /** @var Article|null $data */
                $data = $event->getData();
                if (!$data) {
                    return;
                }
                $this->setupSpecificCategoryTag(
                    $event->getForm(),
                    $tag
                );
            }
        );

    }

    private function setupSpecificCategoryTag(FormInterface $form, ?string $tag)
    {
        if (null === $tag) {
            $form->remove('specificTagName');
            return;
        }

        $form->add('specificTagName', EntityType::class, [
                'class' => Tags::class,
                'query_builder' => function (EntityRepository $er) use ($tag) {
                    return $er->createQueryBuilder('t')
                        ->andwhere('t.category = :tag')
                        ->setParameter('tag', $tag);
                },
                'choice_label' => 'Name',
                'mapped'=>false,
                'multiple'=>true,
                'expanded'=>false,
                'by_reference' => false,

            ]);
        }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'validation_groups' => false,
            'data_class' => Article::class,
            'allow_extra_fields' => true
        ]);
    }
}
