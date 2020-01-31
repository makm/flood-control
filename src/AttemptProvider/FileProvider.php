<?php
/**
 * Maxim Kapkaev makm@km.ru
 * Copyright (c) 21.01.2020 (21:52)
 */

namespace Makm\FloodControl\AttemptProvider;


/**
 * Class FileProvider
 * @package Makm\FloodControl\AttemptProvider
 */
class FileProvider implements AttemptProviderInterface
{
    private const FILENAME_PREFIX = 'flood-control-data';
    private const DATE_FORMAT = 'U.u';

    /**
     * @var string
     */
    private $path;

    /**
     * FileProvider constructor.
     * @param null $path
     */
    public function __construct($path = null)
    {
        $this->path = $path ?: sys_get_temp_dir();
    }

    /**
     * @param $actionKey
     * @return string
     */
    private function getFilename($actionKey): string
    {
        return $this->path.DIRECTORY_SEPARATOR.self::FILENAME_PREFIX.'-'.$actionKey;
    }

    /**
     * @param $actionKey
     * @return array
     */
    private function readDates($actionKey): array
    {
        $filename = $this->getFilename($actionKey);
        if (!file_exists($filename)) {
            return [];
        }

        return \unserialize(file_get_contents($filename));
    }

    /**
     * @param $actionKey
     * @param \DateTime $dateTime
     */
    private function writeDate($actionKey, \DateTime $dateTime): void
    {
        $filename = $this->getFilename($actionKey);
        $dates = $this->readDates($actionKey);
        $dates[] = $dateTime->format(self::DATE_FORMAT);
        sort($dates);
        file_put_contents($filename, \serialize($dates));
    }

    /**
     * @param $dates
     * @param \DateTime $afterDateTime
     * @return array
     */
    private function filterAfter($dates, \DateTime $afterDateTime): array
    {
        return array_filter(
            $dates,
            static function (string $date) use ($afterDateTime) {
                return \DateTime::createFromFormat(self::DATE_FORMAT, $date) >= $afterDateTime;
            }
        );
    }

    /**
     * @inheritDoc
     */
    public function push(string $actionKey, \DateTime $dateTime): void
    {
        $this->writeDate($actionKey, $dateTime);
    }

    /**
     * @inheritDoc
     */
    public function timesAndFirstDateTime(string $actionKey, \DateTime $afterDateTime): array
    {
        $list = $this->filterAfter($this->readDates($actionKey), $afterDateTime);
        $count = count($list);
        $firstDateTime = $list
            ? \DateTime::createFromFormat(self::DATE_FORMAT, \array_shift($list))
            : null;

        return [$count, $firstDateTime];
    }

    /**
     * @inheritDoc
     */
    public function purge($actionKey, \DateTime $beforeDateTime = null): void
    {
        $filename = $this->getFilename($actionKey);
        if (!file_exists($filename)) {
            //do nothing
            return;
        }

        if ($beforeDateTime === null) {
            unlink($filename);

            return;
        }

        $dates = $this->readDates($actionKey);
        $datesFiltred = $this->filterAfter($dates, $beforeDateTime);

        file_put_contents($filename, \serialize($datesFiltred));
    }
}
