<?php
    namespace Larva\Util;

    class RelationUtil
    {
        const ONE_TO_ONE        = 'one-to-one';
        const ONE_TO_ONE_OWNING = 'one-to-one-owning';
        const ONE_TO_MANY       = 'one-to-many';
        const MANY_TO_ONE       = 'many-to-one';
        const MANY_TO_MANY      = 'many-to-many';

        private static $config;

        public static function getFormattedRelations(array $relations, $config)
        {
            static::$config = $config;

            $formatted = array();
            foreach ($relations as $relation) {
                if (!isset($relation['type']))
                    return;

                $ns        = $config['namespace'];
                $type      = $relation['type'];
                $ownership = self::getOwnershipStatement($type);
                $args      = self::getArguments($type, $relation[0], $ns);
                $name      = self::getRelationName($relation[0]);

                $template = <<<HEREDOC
public function %s() {
    return \$this->%s(%s);
}
HEREDOC;

                $function = sprintf($template, $name, $ownership, implode(', ', $args));
                echo $function . "\n";
            }
        }

        private static function getOwnershipStatement($type)
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

            return $ownership;
        }

        private static function getArguments($type, array $relation, $namespace)
        {
            $namespace = str_replace('\\', '\\\\', $namespace);

            $args = array();
            switch ($type) {
                case RelationUtil::ONE_TO_ONE:
                case RelationUtil::ONE_TO_ONE_OWNING:
                case RelationUtil::ONE_TO_MANY:
                case RelationUtil::MANY_TO_ONE:
                    $args[] = "'$namespace\\\\{$relation['relatedTable']}'";
                    $args[] = "'{$relation['foreignKey']}'";
                    break;
                case RelationUtil::MANY_TO_MANY:
                    $args[] = "'$namespace\\\\{$relation['relatedTable']}'";
                    $args[] = "'{$relation['joinTable']}'";
                    $args[] = "'{$relation['foreignKey']}'";
                    $args[] = "'{$relation['joinKey']}'";
                    break;
            }


            return $args;
        }

        private static function getRelationName(array $relation)
        {
            return $relation['relationName'];
        }
    }