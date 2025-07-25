<?php

declare (strict_types=1);
/*
 * This file is part of the Monolog package.
 *
 * (c) Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace _PhpScoper01187d35592a\Monolog\Formatter;

use DateTimeInterface;
/**
 * Format a log message into an Elasticsearch record
 *
 * @author Avtandil Kikabidze <akalongman@gmail.com>
 */
class ElasticsearchFormatter extends NormalizerFormatter
{
    /**
     * @var string Elasticsearch index name
     */
    protected $index;
    /**
     * @var string Elasticsearch record type
     */
    protected $type;
    /**
     * @param string $index Elasticsearch index name
     * @param string $type  Elasticsearch record type
     */
    public function __construct(string $index, string $type)
    {
        // Elasticsearch requires an ISO 8601 format date with optional millisecond precision.
        parent::__construct(DateTimeInterface::ISO8601);
        $this->index = $index;
        $this->type = $type;
    }
    /**
     * {@inheritDoc}
     */
    public function format(array $record)
    {
        $record = parent::format($record);
        return $this->getDocument($record);
    }
    /**
     * Getter index
     *
     * @return string
     */
    public function getIndex() : string
    {
        return $this->index;
    }
    /**
     * Getter type
     *
     * @return string
     */
    public function getType() : string
    {
        return $this->type;
    }
    /**
     * Convert a log message into an Elasticsearch record
     *
     * @param  mixed[] $record Log message
     * @return mixed[]
     */
    protected function getDocument(array $record) : array
    {
        $record['_index'] = $this->index;
        $record['_type'] = $this->type;
        return $record;
    }
}
