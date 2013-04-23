<?php
    namespace Larva;

    use MwbExporter\Model\ForeignKey;

    class FKDumper
    {
        public static function dump(ForeignKey $fk)
        {
//            echo sprintf("Target entity: %s\n", $fk->getOwningTable()->getRawTableName());
//            echo sprintf("Target comment: %s\n", $fk->getOwningTable()->getParameters()->get('comment'));
//            echo sprintf("Is target M-M: %s\n", print_r($fk->getOwningTable()->isManyToMany(), true));
//            echo sprintf("Mapped by: %s\n", $fk->getReferencedTable()->getRawTableName());
//            echo sprintf("Is mapped M-M: %s\n", print_r($fk->getOwningTable()->isManyToMany(), true));
//            echo sprintf("Join column: %s (%s)\n", $fk->getForeign()->getColumnName(), $fk->getForeign()->getColumnType());
//            echo sprintf("Join column owner: %s\n", $fk->getForeign()->getTable()->getRawTableName());
//            echo sprintf("Join column 1-M: %s\n", $fk->getForeign()->hasOneToManyRelation());
//            echo sprintf("M-1: %s\n", $fk->isManyToOne());
//            echo sprintf("Local column: %s (%s)\n", $fk->getLocal()->getColumnName(), $fk->getLocal()->getColumnType());
//            echo sprintf("Local column owner: %s\n", $fk->getLocal()->getTable()->getRawTableName());
//            echo sprintf("Local column 1-M: %s\n", $fk->getLocal()->hasOneToManyRelation());
//            echo sprintf("Pluralized relation name: %s\n", strtolower($fk->getReferencedTable()->getModelNameInPlural()));
//            echo sprintf("Engine: %s\n", $fk->getOwningTable()->getParameters()->get('tableEngine'));
//            echo sprintf("Rules: D(%s) U(%s)\n", $fk->getParameters()->get('deleteRule'), $fk->getParameters()->get('updateRule'));

            $r = new Relation();

            if (!$fk->isManyToOne())
                $r->ownership = 'hasOne';
            elseif ($fk->isManyToOne())
                $r->ownership = 'belongsTo'; elseif ($fk->getLocal()->hasOneToManyRelation())
                $r->ownership = 'hasMany'; elseif ($fk->getForeign()->hasOneToManyRelation())
                $r->ownership = 'belongsToMany';

            $singular = array('hasOne', 'belongsTo');
            $r->name  = lcfirst(in_array($r->ownership, $singular) ? $fk->getReferencedTable()->getModelName() : $fk->getReferencedTable()->getModelNameInPlural());
            $r->model = $fk->getReferencedTable()->getModelName();
            $r->fk = $fk->getLocal()->getColumnName();

            return $r;
        }
    }

    class Relation
    {
        public $name;
        public $ownership;
        public $model;
        public $fk;
    }