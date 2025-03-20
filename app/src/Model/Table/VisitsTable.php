<?php

declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Validation\Validator;

/**
 * Visits Model
 *
 * @property \App\Model\Table\AddressesTable&\Cake\ORM\Association\BelongsTo $Addresses
 *
 * @method \App\Model\Entity\Visit newEmptyEntity()
 * @method \App\Model\Entity\Visit newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\Visit[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Visit get($primaryKey, $options = [])
 * @method \App\Model\Entity\Visit findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\Visit patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Visit[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Visit|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Visit saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Visit[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Visit[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\Visit[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Visit[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class VisitsTable extends Table
{
    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('visits');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');
        $this->hasOne('Addresses')
            ->setClassName('Addresses')
            ->setForeignKey('foreign_id')
            ->setConditions(['Addresses.foreign_table' => 'visits']);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->date('date')
            ->requirePresence('date', 'create')
            ->notEmptyDate('date');

        $validator
            ->boolean('completed')
            ->notEmptyString('completed');

        $validator
            ->integer('forms')
            ->requirePresence('forms', 'create')
            ->notEmptyString('forms');

        $validator
            ->integer('products')
            ->requirePresence('products', 'create')
            ->notEmptyString('products');

        $validator
            ->integer('duration')
            ->notEmptyString('duration');

        $validator
            ->integer('address_id')
            ->notEmptyString('address_id');

        return $validator;
    }

    public function validationRequest(Validator $validator): Validator
    {
        $validator
            ->requirePresence('date', 'create', '(date) | "Data da visita" é obrigatória')
            ->date('date', ['ymd'], 'Formato inválido (YYYY-MM-DD)')
            ->notEmptyDate('date', '(date) | "Data da visita" não pode ser vazia');

        $validator
            ->requirePresence('forms', 'create', '(forms) | "Formulário" é obrigatório')
            ->integer('forms', 'O número de (forms) | "Formulários" deve ser um valor inteiro')
            ->greaterThanOrEqual('forms', 0, 'O número de (forms) | "Formulários" não pode ser negativo');

        $validator
            ->requirePresence('products', 'create', '(products) | "Produto" é obrigatório')
            ->integer('products', 'O número de (products) | "Produtos" deve ser um valor inteiro')
            ->greaterThanOrEqual('products', 0, 'O número de (products) | "Produtos" não pode ser negativo');

        $validator
            ->allowEmptyString('completed')
            ->integer('completed', '(completed) | "Status concluído" deve ser um número inteiro')
            ->inList('completed', [0, 1], '(completed) | "Status" deve ser 0 (pendente) ou 1 (concluído)');


        $addressTable = TableRegistry::getTableLocator()->get('Addresses');
        $addressValidator = new \Cake\Validation\Validator();

        $addressTable->validationRequest($addressValidator);
        $validator->addNested('address', $addressValidator);

        return $validator;
    }
}
