<?php
    namespace Larva;

    use MwbExporter\Formatter\FormatterInterface;
    use MwbExporter\Logger\Logger;
    use MwbExporter\Logger\LoggerConsole;
    use MwbExporter\Logger\LoggerFile;
    use MwbExporter\Model\Document;
    use MwbExporter\Storage\LoggedStorage;

    class Bootstrap
    {
        const FILE_STORAGE   = 'file';
        const LOGGED_STORAGE = 'logged';
        const ZIP_STORAGE    = 'zip';

        public static function generate($filename, $options, $outputDirectory, $storage = self::FILE_STORAGE)
        {
            $formatter = new Formatter();
            $formatter->setup($options);

            $storage = static::getStorage($storage);
            $storage->setOutdir(realpath($outputDirectory) ? realpath($outputDirectory) : $outputDirectory);

            $writer = static::getWriter($formatter->getPreferredWriter());
            $writer->setStorage($storage);

            $document = new Document($formatter, $filename);

            $logger = new Logger();

            $document->setLogger($logger);
            $document->write($writer);

            if ($e = $document->getError()) {
                throw $e;
            }

            return $document;
        }

        /**
         * Get writer.
         *
         * @param string $name  The writer name
         *
         * @throws \InvalidArgumentException
         * @return \MwbExporter\Writer\WriterInterface
         */
        private static function getWriter($name)
        {
            $class = sprintf('\\MwbExporter\\Writer\\%sWriter', ucfirst($name));
            if (class_exists($class)) {
                $writter = new $class();

                return $writter;
            }

            throw new \InvalidArgumentException(sprintf('Writer %s not found.', $class));
        }

        /**
         * Get storage.
         *
         * @param string $name  The storage name
         *
         * @throws \InvalidArgumentException
         * @return \MwbExporter\Storage\StorageInterface
         */
        private static function getStorage($name)
        {
            $class = sprintf('\\MwbExporter\\Storage\\%sStorage', ucfirst($name));
            if (class_exists($class)) {
                $storage = new $class();

                return $storage;
            }

            throw new \InvalidArgumentException(sprintf('Storage %s not found.', $class));
        }
    }