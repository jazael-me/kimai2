<?php

/*
 * This file is part of the Kimai time-tracking app.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Form\Toolbar;

use App\Form\Type\ActivityType;
use App\Form\Type\CustomerType;
use App\Form\Type\PageSizeType;
use App\Form\Type\ProjectType;
use App\Form\Type\UserRoleType;
use App\Form\Type\UserType;
use App\Form\Type\VisibilityType;
use App\Repository\ActivityRepository;
use App\Repository\CustomerRepository;
use App\Repository\ProjectRepository;
use App\Repository\Query\ActivityQuery;
use App\Repository\Query\CustomerQuery;
use App\Repository\Query\ProjectQuery;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Defines the base form used for all toolbars.
 *
 * Extend this class and stack the elements defined here, they are coupled to each other and with the toolbar.js.
 */
abstract class AbstractToolbarForm extends AbstractType
{
    /**
     * Dirty hack to enable easy handling of GET form in controller and javascript.
     *Cleans up the name of all form elents (and unfortunately of the form itself).
     *
     * @return null|string
     */
    public function getBlockPrefix()
    {
        return '';
    }

    /**
     * @param FormBuilderInterface $builder
     */
    protected function addUserChoice(FormBuilderInterface $builder)
    {
        $builder->add('user', UserType::class, [
            'label' => 'label.user',
            'required' => false,
        ]);
    }

    /**
     * @param FormBuilderInterface $builder
     */
    protected function addCustomerChoice(FormBuilderInterface $builder)
    {
        $builder->add('customer', CustomerType::class, [
            'required' => false,
            'query_builder' => function (CustomerRepository $repo) {
                $query = new CustomerQuery();
                $query->setVisibility(CustomerQuery::SHOW_BOTH); // this field is the reason for the query here
                $query->setResultType(CustomerQuery::RESULT_TYPE_QUERYBUILDER);
                $query->setOrderBy('name');

                return $repo->findByQuery($query);
            },
        ]);
    }

    /**
     * @param FormBuilderInterface $builder
     * @param null|string $label
     */
    protected function addVisibilityChoice(FormBuilderInterface $builder, ?string $label = null)
    {
        $builder->add('visibility', VisibilityType::class, [
            'required' => false,
            'placeholder' => null,
            'label' => $label
        ]);
    }

    /**
     * @param FormBuilderInterface $builder
     */
    protected function addPageSizeChoice(FormBuilderInterface $builder)
    {
        $builder->add('pageSize', PageSizeType::class, [
            'required' => false,
        ]);
    }

    /**
     * @param FormBuilderInterface $builder
     */
    protected function addUserRoleChoice(FormBuilderInterface $builder)
    {
        $builder->add('role', UserRoleType::class, [
            'required' => false,
        ]);
    }

    /**
     * @param FormBuilderInterface $builder
     */
    protected function addStartDateChoice(FormBuilderInterface $builder)
    {
        $builder->add('begin', DateType::class, [
            'label' => 'label.begin',
            'widget' => 'single_text',
            'html5' => false,
            'required' => false,
            'format' => DateType::HTML5_FORMAT,
            'attr' => ['autocomplete' => 'off', 'data-datepicker' => 'on'],
            'empty_data' => (new \DateTime('first day of this month'))->format('Y-M-d')
        ]);
    }

    /**
     * @param FormBuilderInterface $builder
     */
    protected function addEndDateChoice(FormBuilderInterface $builder)
    {
        $builder->add('end', DateType::class, [
            'label' => 'label.end',
            'widget' => 'single_text',
            'html5' => false,
            'required' => false,
            'format' => DateType::HTML5_FORMAT,
            'attr' => ['autocomplete' => 'off', 'data-datepicker' => 'on'],
            'empty_data' => (new \DateTime('last day of this month'))->format('Y-M-d')
        ]);
    }

    /**
     * @param FormBuilderInterface $builder
     */
    protected function addProjectChoice(FormBuilderInterface $builder)
    {
        $builder->add('project', ChoiceType::class, [
            'group_by' => null,
            'required' => false,
            'label' => 'label.project',
        ]);

        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) {
                $data = $event->getData();
                if (!isset($data['customer']) || empty($data['customer'])) {
                    return;
                }

                $event->getForm()->add('project', ProjectType::class, [
                    'group_by' => null,
                    'required' => false,
                    'query_builder' => function (ProjectRepository $repo) use ($data) {
                        $query = new ProjectQuery();
                        $query->setCustomer($data['customer']);
                        $query->setResultType(ProjectQuery::RESULT_TYPE_QUERYBUILDER);
                        $query->setVisibility(ProjectQuery::SHOW_BOTH);
                        $query->setOrderBy('name');

                        return $repo->findByQuery($query);
                    },
                ]);
            }
        );
    }

    /**
     * @param FormBuilderInterface $builder
     */
    protected function addActivityChoice(FormBuilderInterface $builder)
    {
        $builder->add('activity', ActivityType::class, [
            'required' => false,
            'query_builder' => function (ActivityRepository $repo) {
                $query = new ActivityQuery();
                $query->setResultType(ActivityQuery::RESULT_TYPE_QUERYBUILDER);
                $query->setGlobalsOnly(true);
                $query->setOrderGlobalsFirst(true);
                $query->setVisibility(ActivityQuery::SHOW_BOTH);
                $query->setOrderBy('name');

                return $repo->findByQuery($query);
            },
        ]);

        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) {
                $data = $event->getData();
                if (!isset($data['project']) || empty($data['project'])) {
                    return;
                }

                $event->getForm()->add('activity', ActivityType::class, [
                    'required' => false,
                    'query_builder' => function (ActivityRepository $repo) use ($data) {
                        $query = new ActivityQuery();
                        $query->setResultType(ActivityQuery::RESULT_TYPE_QUERYBUILDER);
                        $query->setProject($data['project']);
                        $query->setOrderGlobalsFirst(true);
                        $query->setVisibility(ActivityQuery::SHOW_BOTH);
                        $query->setOrderBy('name');

                        return $repo->findByQuery($query);
                    },
                ]);
            }
        );
    }
}
