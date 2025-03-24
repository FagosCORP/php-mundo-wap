<?php

declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Workdays Model
 *
 * @method \App\Model\Entity\Workday newEmptyEntity()
 * @method \App\Model\Entity\Workday newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\Workday[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Workday get($primaryKey, $options = [])
 * @method \App\Model\Entity\Workday findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\Workday patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Workday[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Workday|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Workday saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Workday[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Workday[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\Workday[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Workday[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class WorkdaysTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('workdays');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->hasMany('Visits')
            ->setClassName('Visits')
            ->setForeignKey('date')
            ->setBindingKey('date')
            ->setProperty('related_visits');
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->date('date')
            ->requirePresence('date', 'create')
            ->notEmptyDate('date');

        $validator
            ->integer('visits')
            ->notEmptyString('visits');

        $validator
            ->integer('completed')
            ->notEmptyString('completed');

        $validator
            ->integer('duration')
            ->notEmptyString('duration');

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
            ->requirePresence('date', 'create', '(date) | "data do dia útil" é obrigatória')
            ->notEmptyDate('date', '(date) | "data do dia útil" não pode ser vazia');

        $validator
            ->allowEmptyString('visits')
            ->integer('visits', '(visits) | "número de visitas" deve ser um valor inteiro')
            ->greaterThanOrEqual('visits', 0, '(visits) | "número de visitas" não pode ser negativo');

        $validator
            ->allowEmptyString('completed')
            ->integer('completed', '(completed) | "número de visitas concluídas" deve ser um valor inteiro')
            ->greaterThanOrEqual('completed', 0, '(completed) | "número de visitas concluídas" não pode ser negativo');

        $validator
            ->allowEmptyString('duration')
            ->integer('duration', '(duration) | "duração total" deve ser um valor inteiro em minutos')
            ->greaterThanOrEqual('duration', 0, '(duration) | "duração total" não pode ser negativa')
            ->lessThanOrEqual('duration', 480, 'Limite de horas atingido (máximo de 8h) para (duration)');

        return $validator;
    }
}
