<?php
    namespace Larva\Model;

    use MwbExporter\Helper\Pluralizer;
    use MwbExporter\Model\Column as BaseColumn;
    use MwbExporter\Writer\WriterInterface;

    class Column extends BaseColumn
    {
        const ONE_TO_ONE        = '1-1';
        const ONE_TO_ONE_OWNING = '1-1o';
        const ONE_TO_MANY       = '1-n';
        const MANY_TO_ONE       = 'n-1';
        const MANY_TO_MANY      = 'n-m';

        public function write(WriterInterface $writer)
        {
//            print_r(array(
//                'name'      => $this->getColumnName(),
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
            $formatter = $this->getDocument()->getFormatter();

            $relations = array();

            // one to many references
            foreach ($this->foreigns as $foreign) {
                $targetEntity    = $foreign->getOwningTable()->getModelName();
                $targetEntityRaw = $foreign->getOwningTable()->getRawTableName();
                $mappedBy        = $foreign->getReferencedTable()->getModelName();
                $mappedByRaw     = $foreign->getReferencedTable()->getRawTableName();

                if ($foreign->getForeign()->getTable()->isManyToMany()) {
                    $comment = $foreign->getReferencedTable()->getParameters()->get('comment');
                    if ($comment) {
                        $comment = json_decode($comment, true);

                        //
                        //
                        //  Add all relations for each table into a lookup
                        //  if duplicates for relationship, assume self-referential
                        //  ...experiment with self-referential m-m
                        //
                        //


                        $data = array(
                            'type' => self::MANY_TO_MANY,
                            array(
                                'relationName' => lcfirst(Pluralizer::pluralize($comment['m2m'])),
                                'relatedTable' => $comment['m2m'],
                                'joinTable'    => $foreign->getOwningTable()->getRawTableName(),
                                'foreignKey'   => $foreign->getOwningTable()->getRelationToTable($mappedByRaw)->getForeign()->getColumnName(),
                                'joinKey'      => $foreign->getOwningTable()->getRelationToTable($comment['m2m'])->getForeign()->getColumnName()
                            )
                        );

                        $relations[] = $data;
                    }

                    continue;
                }

                $joinColumnAnnotationOptions = array(
                    'name'                 => $foreign->getForeign()->getColumnName(),
                    'referencedColumnName' => $foreign->getLocal()->getColumnName(),
                    'nullable'             => !$foreign->getForeign()->getParameters()->get('isNotNull') ? null : false,
                );

                //check for OneToOne or OneToMany relationship
                if ($foreign->isManyToOne()) { // is OneToMany
                    $related = $this->getRelatedName($foreign);

//                    $data = array(
//                        'type' => self::MANY_TO_MANY,
//                        array(
//                            'relatedTable' => $comment['m2m'],
//                            'joinTable'    => $foreign->getOwningTable()->getRawTableName(),
//                            'foreignKey'   => $foreign->getOwningTable()->getRelationToTable($mappedByRaw)->getForeign()->getColumnName(),
//                            'joinKey'      => $foreign->getOwningTable()->getRelationToTable($comment['m2m'])->getForeign()->getColumnName()
//                        )
//                    );

//                    $relations[] = $data;

                    $relations[] = '1-m ' . lcfirst(Pluralizer::pluralize($targetEntity)) . $related;
                    echo "1-m\n";
                    echo lcfirst(Pluralizer::pluralize($targetEntity)) . $related . "\n";
                    $this->writeRelation($writer, self::ONE_TO_MANY, $foreign->getForeign()->getColumnName());
                } else { // is OneToOne

                    $relations[] = '1-1 ' . lcfirst($targetEntity);
                    echo "1-1\n";
                    echo lcfirst($targetEntity) . "\n";

                    if ($this->getTable()->getRawTableName() == $foreign->getReferencedTable()->getRawTableName())
                        $this->writeRelation($writer, self::ONE_TO_ONE_OWNING, $foreign->getForeign()->getColumnName());
                    else
                        $this->writeRelation($writer, self::ONE_TO_ONE, $foreign->getForeign()->getColumnName());
                }
            }
            // many to references
            if (null !== $this->local) {
                $targetEntity = $this->local->getReferencedTable()->getModelName();
                $inversedBy   = $this->local->getOwningTable()->getModelName();

                $annotationOptions           = array(
                    'targetEntity' => $targetEntity,
                    'mappedBy'     => null,
                    'inversedBy'   => $inversedBy,
                );
                $joinColumnAnnotationOptions = array(
                    'name'                 => $this->local->getForeign()->getColumnName(),
                    'referencedColumnName' => $this->local->getLocal()->getColumnName(),
                    'nullable'             => !$this->local->getForeign()->getParameters()->get('isNotNull') ? null : false,
                );

                //check for OneToOne or ManyToOne relationship
                if ($this->local->isManyToOne()) { // is ManyToOne
                    $related    = $this->getManyToManyRelatedName($this->local->getReferencedTable()->getRawTableName(), $this->local->getForeign()->getColumnName());
                    $refRelated = $this->local->getLocal()->getRelatedName($this->local);

                    $annotationOptions['inversedBy'] = lcfirst(Pluralizer::pluralize($annotationOptions['inversedBy'])) . $refRelated;

                    $relations[] = 'm-1 ' . lcfirst($targetEntity) . $related;
                    echo "m-1\n" . lcfirst($targetEntity) . $related . "\n";
                    $this->writeRelation($writer, self::MANY_TO_ONE, $this->local->getForeign()->getColumnName());

                } else { // is OneToOne
                    if ($this->local->parseComment('unidirectional') === 'true') {
                        $annotationOptions['inversedBy'] = null;
                    } else {
                        $annotationOptions['inversedBy'] = lcfirst($annotationOptions['inversedBy']);
                    }

                    $relations[] = '1-1 ' . lcfirst($targetEntity);
                    echo "1-1\n" . lcfirst($targetEntity) . "\n";
                    if ($this->getTable()->getRawTableName() == $this->local->getReferencedTable()->getRawTableName())
                        $this->writeRelation($writer, self::ONE_TO_ONE_OWNING, $this->local->getForeign()->getColumnName());
                    else
                        $this->writeRelation($writer, self::ONE_TO_ONE, $this->local->getForeign()->getColumnName());
                }
            }

            $this->getParent()->setRelationsForTable($this->getTable()->getModelName(), $relations);
            return $this;
        }

        private function writeRelation(WriterInterface $writer, $type, $args = array(''))
        {
            $ownership = '';

            switch ($type) {
                case self::ONE_TO_ONE:
                    $ownership = 'belongsTo';
                    break;
                case self::ONE_TO_ONE_OWNING:
                    $ownership = 'hasOne';
                    break;
                case self::ONE_TO_MANY:
                    $ownership = 'hasMany';
                    break;
                case self::MANY_TO_ONE:
                    $ownership = 'belongsTo';
                    break;
                case self::MANY_TO_MANY:
                    $ownership = 'belongsToMany';
                    break;
            }

            echo "\t" . $ownership . ":" . var_export($args, true) . "\n";
        }
    }