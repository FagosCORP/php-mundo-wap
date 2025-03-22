<?php

declare(strict_types=1);

namespace App\Model\Table;

use App\Exceptions\LimitHoursDailyException;
use App\Service\Workday\LimitWorkday;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use Cake\ORM\TableRegistry;

class VisitsTable extends Table
{
    private LimitWorkday $limitWorkday;

    public function __construct(
        $config = []
    ) {
        parent::__construct($config);
        $this->limitWorkday = new LimitWorkday();
    }

    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('visits');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->hasOne('Addresses')
            ->setClassName('Addresses')
            ->setForeignKey('foreign_id');
    }

    public function beforeSave($event, $entity, $options): bool
    {
        $haveLimit = true;
        $isNewCompleted = $entity->isNew() && $entity->completed;

        if ($isNewCompleted) {
            $haveLimit = $this->limitWorkday->have(
                $entity->date->toDateString(),
                $entity->duration,
            );
        }

        if (!$haveLimit) {
            throw new LimitHoursDailyException();
        }

        return true;
    }
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->date('date')
            ->requirePresence('date', 'create')
            ->notEmptyDate('date', 'A data da visita é obrigatória');

        $validator
            ->boolean('completed')
            ->notEmptyString('completed', 'O status de conclusão é obrigatório');

        $validator
            ->integer('forms')
            ->requirePresence('forms', 'create')
            ->notEmptyString('forms', 'O número de formulários é obrigatório');

        $validator
            ->integer('products')
            ->requirePresence('products', 'create')
            ->notEmptyString('products', 'O número de produtos é obrigatório');

        $validator
            ->integer('duration')
            ->notEmptyString('duration', 'A duração é obrigatória');

        $validator
            ->integer('address_id')
            ->notEmptyString('address_id', 'O ID do endereço é obrigatório');

        return $validator;
    }

    public function validationDateParam(Validator $validator): Validator
    {
        $validator
            ->date('date', ['ymd'], 'Formato inválido (YYYY-MM-DD)')
            ->requirePresence('date', 'create', 'A data é obrigatória.')
            ->notEmptyString('date', 'A data não pode ser vazia.');

        return $validator;
    }


    public function validationRequest(Validator $validator): Validator
    {
        $validator
            ->requirePresence('date', 'create', 'A data da visita é obrigatória')
            ->date('date', ['ymd'], 'Formato inválido (YYYY-MM-DD)')
            ->notEmptyDate('date', 'A data da visita não pode ser vazia');

        $validator
            ->requirePresence('forms', 'create', 'O formulário é obrigatório')
            ->integer('forms', 'O número de formulários deve ser um valor inteiro')
            ->greaterThanOrEqual('forms', 0, 'O número de formulários não pode ser negativo');

        $validator
            ->requirePresence('products', 'create', 'O produto é obrigatório')
            ->integer('products', 'O número de produtos deve ser um valor inteiro')
            ->greaterThanOrEqual('products', 0, 'O número de produtos não pode ser negativo');

        $validator
            ->allowEmptyString('completed')
            ->integer('completed', 'O status de conclusão deve ser um número inteiro')
            ->inList('completed', [0, 1], 'O status deve ser 0 (pendente) ou 1 (concluído)');

        $addressTable = TableRegistry::getTableLocator()->get('Addresses');
        $addressValidator = new Validator();
        $addressTable->validationRequest($addressValidator);
        $validator->addNested('address', $addressValidator);

        return $validator;
    }
}
