<?php

/*
 * This file is part of the Kimai time-tracking app.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Form\Toolbar;

use App\Repository\Query\TimesheetQuery;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Defines the form used for filtering the timesheet.
 */
class TimesheetToolbarForm extends AbstractToolbarForm
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->addTimesheetStateChoice($builder);
        $this->addPageSizeChoice($builder);
        $this->addStartDateChoice($builder);
        $this->addEndDateChoice($builder);
        $this->addCustomerChoice($builder);
        $this->addProjectChoice($builder);
        $this->addActivityChoice($builder);
    }

    /**
     * @param FormBuilderInterface $builder
     */
    protected function addTimesheetStateChoice(FormBuilderInterface $builder)
    {
        $builder->add('state', ChoiceType::class, [
            'label' => 'label.entryState',
            'required' => false,
            'placeholder' => null,
            'choices' => [
                'entryState.all' => TimesheetQuery::STATE_ALL,
                'entryState.running' => TimesheetQuery::STATE_RUNNING,
                'entryState.stopped' => TimesheetQuery::STATE_STOPPED
            ],
            //'attr' => ['class' => 'selectpicker', 'data-live-search' => false, 'data-width' => '100%']
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => TimesheetQuery::class,
            'csrf_protection' => false,
        ]);
    }
}
