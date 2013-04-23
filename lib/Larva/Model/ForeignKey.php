<?php
    namespace Larva\Model;

    use MwbExporter\Model\ForeignKey as BaseForeignKey;
    use MwbExporter\Writer\WriterInterface;

    class ForeignKey extends BaseForeignKey
    {
        public function write(WriterInterface $writer)
        {
//            $writer->write(">>>>>".$this->getLocal()->getParameters()->get('name'));
//            $writer->write(">>>>>".$this->getForeign()->getParameters()->get('name'));
//            $writer->write(">>>>>".$this->getForeign()->getTable()->getRawTableName());
//            $writer->write(">>>>>".$this->getOwningTable()->getRawTableName());
//            $writer->write(">>>>>".$this->getLocal()->getTable()->getRawTableName());
        }

    }