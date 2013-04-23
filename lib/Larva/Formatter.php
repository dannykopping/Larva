<?php
    namespace Larva;

    use MwbExporter\Formatter\Formatter as BaseFormatter;
    use MwbExporter\Formatter\FormatterInterface;
    use MwbExporter\Model\Base;

    class Formatter extends BaseFormatter
    {
        const CFG_USE = 'use';
        const CFG_EXTENDS = 'extends';
        const CFG_NAMESPACE = 'namespace';

        protected function init()
        {
            $this->setDatatypeConverter(new DatatypeConverter());
            $this->addConfigurations(array(
                static::CFG_USE => array('Illuminate\Database\Eloquent\Model'),
                static::CFG_EXTENDS => 'Model',
                static::CFG_NAMESPACE => 'My\\Namespace',
                FormatterInterface::CFG_INDENTATION => 4,
            ));
        }

        public function createTable(Base $parent, $node)
        {
            return new Model\Table($parent, $node);
        }

        public function createColumn(Base $parent, $node)
        {
            return new Model\Column($parent, $node);
        }

        public function createColumns(Base $parent, $node)
        {
            return new Model\Columns($parent, $node);
        }

        public function createForeignKey(Base $parent, $node)
        {
            return new Model\ForeignKey($parent, $node);
        }


        /**
         * Get formatter title.
         *
         * @return string
         */
        public function getTitle()
        {
            return 'Laravel 4';
        }

        /**
         * Get file extension for generated code.
         *
         * @return string
         */
        public function getFileExtension()
        {
            return 'php';
        }
    }