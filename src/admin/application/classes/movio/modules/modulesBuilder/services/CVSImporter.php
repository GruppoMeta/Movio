<?php
class movio_modules_modulesBuilder_services_CVSImporter implements Iterator
{
    protected $worksheet;
    protected $data;
    protected $pos;

    public function __construct($options)
    {
        set_time_limit(0);
        ini_set('memory_limit', '512M');
        glz_importApplicationLib('PHPExcel/Classes/PHPExcel/IOFactory.php');
        $csv = new PHPExcel_Reader_CSV();
        $csv->setDelimiter($options['delimiter']);
        $csv->setEnclosure($options['enclosure']);
        $excel = $csv->load($options['filePath']);
        $this->worksheet = $excel->getActiveSheet();
    }

    public function getHeading()
    {
        $maxCol = $this->worksheet->getHighestDataColumn();
        $data = $this->worksheet->rangeToArray('A1:'.$maxCol.'1');
        return $data[0];
    }

    public function count() {
        return $this->worksheet->getHighestDataRow()-1;
    }

    public function &current()
    {
        return $this->data;
    }

    public function key()
    {
        return $this->pos;
    }

    public function next()
    {
        $this->fetch();
    }

    public function rewind()
    {
        $this->pos = 0;
        $this->fetch();
    }

    public function valid()
    {
        return $this->pos <= $this->worksheet->getHighestDataRow()-1;
    }

    protected function fetch()
    {
        $i = $this->pos + 2;
        $maxCol = $this->worksheet->getHighestDataColumn();
        $data = $this->worksheet->rangeToArray('A'.$i.':'.$maxCol.$i);

        if (!is_null($data[0][0])) {
            $this->data = new stdClass();
            foreach ($data[0] as $k => $v) {
                $this->data->$k = $v;
            }
        } else {
            $this->data = null;
        }

        $this->pos++;
    }
}