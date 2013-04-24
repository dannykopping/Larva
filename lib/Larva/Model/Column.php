<?php
    namespace Larva\Model;

    use Larva\Util\RelationUtil;
    use MwbExporter\Helper\Pluralizer;
    use MwbExporter\Model\Column as BaseColumn;
    use MwbExporter\Writer\WriterInterface;

    class Column extends BaseColumn
    {
        public function write(WriterInterface $writer)
        {
//            print_r(array(
//                'name'      => $this->geColumnName(),
//                'type'      => $this->getColumnType(),
//                'vars'      => $this->getVars(),
//                'primary'   => $this->isPrimary,
//                'unique'    => $this->isUnique,
//                'lfk'       => $this->getLocalForeignKey() ? $this->getLocalForeignKey()->getParameters()->get('name') : '',
//                'model'     => $this->getTable()->getModelName(),
//                'defVal'    => $this->getParameters()->get('defaultValue'),
//                'len'       => $this->getParameters()->get('length'),
//                'precision' => $this->getParameters()->get('precision'),
//                'scale'     => $this->getParameters()->get('scale'),
//                'comment'   => $this->getParameters()->get('comment'),
//                'notNull'   => $this->getParameters()->get('isNotNull'),
//                '1-m'       => $this->hasOneToManyRelation(),
//                'foreign'   => $this->isForeignKey(),
//            ));
//
//            print_r($this->getParameters());
        }

        public function isForeignKey()
        {
            return in_array($this, $this->foreigns);
        }

        public function writeRelations(WriterInterface $writer)
        {
            $relations = array();

            // one to many references
            foreach ($this->foreigns as $foreign) {
                $targetEntity = $foreign->getOwningTable()->getModelName();
                $mappedBy     = $foreign->getReferencedTable()->getModelName();
                $mappedByRaw  = $foreign->getReferencedTable()->getRawTableName();

                if ($foreign->getForeign()->getTable()->isManyToMany()) {
                    $comment = trim($foreign->getReferencedTable()->getParameters()->get('comment'));
                    if (!empty($comment)) {
                        $comments = json_decode($comment);

                        if (!is_array($comments))
                            $comments = array($comments);

                        foreach ($comments as $comment) {
                            if (strtolower($comment->via) != strtolower($foreign->getOwningTable()->getRawTableName()))
                                continue;

                            $data = array(
                                'type' => RelationUtil::MANY_TO_MANY,
                                array(
                                    'relationName' => lcfirst(Pluralizer::pluralize($comment->m2m)),
                                    'relatedTable' => $comment->m2m,
                                    'joinTable'    => $foreign->getOwningTable()->getRawTableName(),
                                    'foreignKey'   => $foreign->getOwningTable()->getRelationToTable($mappedByRaw)->getForeign()->getColumnName(),
                                    'joinKey'      => $foreign->getOwningTable()->getRelationToTable($comment->m2m)->getForeign()->getColumnName()
                                )
                            );

                            $relations[] = $data;
                        }
                    }

                    continue;
                }

                //check for OneToOne or OneToMany relationship
                if ($foreign->isManyToOne()) { // is OneToMany
                    $data = array(
                        'type' => RelationUtil::ONE_TO_MANY,
                        array(
                            'relationName' => lcfirst(Pluralizer::pluralize($targetEntity)),
                            'relatedTable' => $targetEntity,
                            'foreignKey'   => $foreign->getForeign()->getColumnName(),
                        )
                    );

                    $relations[] = $data;
                } else { // is OneToOne

                    $data = array(
                        'type' => $this->getTable()->getRawTableName() == $foreign->getReferencedTable()->getRawTableName()
                            ? RelationUtil::ONE_TO_ONE_OWNING : RelationUtil::ONE_TO_ONE,
                        array(
                            'relationName' => lcfirst($targetEntity),
                            'relatedTable' => $targetEntity,
                            'foreignKey'   => $foreign->getForeign()->getColumnName(),
                        )
                    );

                    $relations[] = $data;
                }
            }
            // many to references
            if (null !== $this->local) {
                $targetEntity = $this->local->getReferencedTable()->getModelName();

                //check for OneToOne or ManyToOne relationship
                if ($this->local->isManyToOne()) { // is ManyToOne
                    $data = array(
                        'type' => RelationUtil::MANY_TO_ONE,
                        array(
                            'relationName' => lcfirst($targetEntity),
                            'relatedTable' => $targetEntity,
                            'foreignKey'   => $this->local->getForeign()->getColumnName(),
                        )
                    );

                    $relations[] = $data;

                } else { // is OneToOne
                    $data = array(
                        'type' => $this->getTable()->getRawTableName() == $this->local->getReferencedTable()->getRawTableName()
                            ? RelationUtil::ONE_TO_ONE_OWNING : RelationUtil::ONE_TO_ONE,
                        array(
                            'relationName' => lcfirst($targetEntity),
                            'relatedTable' => $targetEntity,
                            'foreignKey'   => $this->local->getForeign()->getColumnName(),
                        )
                    );

                    $relations[] = $data;
                }
            }

            $this->getParent()->setRelationsForTable($this->getTable()->getModelName(), $relations);
            return $this;
        }

        private function writeRelation(WriterInterface $writer, $type, $args = array(''))
        {
            $ownership = '';

            switch ($type) {
                case RelationUtil::ONE_TO_ONE:
                    $ownership = 'belongsTo';
                    break;
                case RelationUtil::ONE_TO_ONE_OWNING:
                    $ownership = 'hasOne';
                    break;
                case RelationUtil::ONE_TO_MANY:
                    $ownership = 'hasMany';
                    break;
                case RelationUtil::MANY_TO_ONE:
                    $ownership = 'belongsTo';
                    break;
                case RelationUtil::MANY_TO_MANY:
                    $ownership = 'belongsToMany';
                    break;
            }

            echo "\t" . $ownership . ":" . var_export($args, true) . "\n";
        }
    }