<?php
 
interface IntGenerator
{
    public function get(): int;
}
 
class UniqueIntGenerator implements IntGenerator
{
    private array $lookup = [];
    private int $n = 0;
    public function __construct(
        public readonly int $min,
        public readonly int $max,
        public readonly int $capacity,
    ) {
        if ($capacity >= $max or $max - $capacity + 1 < $min) {
            throw new RangeException("Невозможно получить $capacity уникальных чисел в диапазоне от $min до $max." . PHP_EOL);
        }
    }
 
    public function get(): int
    {
        if ($this->n >= $this->capacity) {
            throw new RuntimeException("Превышен допустимый объем чисел: $this->capacity." . PHP_EOL);
        }
 
        do {
            $num = rand($this->min, $this->max);
        } while(array_key_exists($num, $this->lookup));
 
        $this->lookup[$num] = null;
        $this->n++;
 
        return $num;
    }
}
 
class IntegerMatrix implements IteratorAggregate
{
    public function __construct(
        public readonly int $length,
        public readonly int $height,
        public readonly IntGenerator $generator,
    ) {}
 
    public function getIterator(): Traversable
    {
        for ($i = 0; $i < $this->height; $i++) {
            yield array_map(
                fn () => str_pad($this->generator->get(), 4, "\040"),
                array_fill(0, $this->length, null),
            );
        }    
    }
}

trait MatrixToArrayConverterTrait
{
    public function convert(IteratorAggregate $iterator): array
    {
        $stringArray = array();
            $i = 0;
            foreach ($iterator as $str) {
                $stringArray[$i] = $str;
                $i++;
            }

        return $stringArray;
    }
}

trait ArrayColumnSumTrait
{
    public function sum(array $array)
    {
        $result = array();
        for($i = 0; $i < count($array); $i++) {
            foreach($array[$i] as $key => $num) {
                if(isset($result[$key]))
                    $result[$key] += $num;
                else
                    $result[$key] = $num;
            }
        }
        return $result;
    }
}
 
class Printer
{
    use MatrixToArrayConverterTrait, ArrayColumnSumTrait;

    public function __construct(
        public readonly string $separator,
    ) {}
 
    public function print(IteratorAggregate $iterator): void
    {
        $stringArray = $this->convert($iterator);
        foreach ($stringArray as $str) {
            echo implode("\040", $str) . $this->separator;            
        }

        echo $this->separator;

        foreach ($stringArray as $key => $string) {
            echo "Cумма строки №" . $key + 1 . " : " . array_sum($string) . $this->separator;  
        }  

        echo $this->separator;

        foreach($this->sum($stringArray) as $key => $sum) {
            echo "Сумма колонки №" . $key + 1 . " : ". $sum . $this->separator;
        }
    }
}
 
$echoer = new Printer(PHP_EOL);
$generator = new UniqueIntGenerator(1, 1000, 5 * 7);
$matrix = new IntegerMatrix(5, 7, $generator);
$echoer->print($matrix);
