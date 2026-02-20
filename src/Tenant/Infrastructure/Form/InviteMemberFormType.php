<?php

declare(strict_types=1);

namespace GardenManager\Tenant\Infrastructure\Form;

use GardenManager\Tenant\Application\Dto\InviteMemberDto;
use GardenManager\Tenant\Domain\Enum\TenantMembershipRole;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class InviteMemberFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'email',
                EmailType::class,
                [
                    'label' => 'Email address',
                ]
            )
            ->add(
                'role',
                EnumType::class,
                [
                    'class' => TenantMembershipRole::class,
                    'label' => 'Role',
                ]
            )
            ->add(
                'submit',
                SubmitType::class,
                [
                    'label' => 'Invite Member',
                    'row_attr' => ['class' => 'flex justify-end mt-4'],
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => InviteMemberDto::class,
        ]);
    }
}
