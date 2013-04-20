<?php
    namespace Larva\Model;

    use MwbExporter\Model\ForeignKey as BaseForeignKey;
    use MwbExporter\Writer\WriterInterface;

    class ForeignKey extends BaseForeignKey
    {
        public function write(WriterInterface $writer)
        {
            $writer->write($this->getLocal()->getParameters()->get('name'));
//            var_dump($this); die();
        }
    }