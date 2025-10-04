<?php

declare(strict_types=1);

use Carbon\Carbon;
use Efati\ModuleGenerator\Casts\GoliDateCast;
use Efati\ModuleGenerator\Support\Goli;
use Efati\ModuleGenerator\Support\HasGoliDates;

require __DIR__ . '/../vendor/autoload.php';

if (! interface_exists(\Illuminate\Contracts\Database\Eloquent\CastsAttributes::class)) {
    require __DIR__ . '/stubs/CastsAttributes.php';
}

if (! class_exists(Carbon::class)) {
    fwrite(STDERR, "The Carbon dependency is required. Run 'composer install' inside the package before executing this script." . PHP_EOL);

    exit(1);
}

class SampleEvent
{
    use HasGoliDates;

    /**
     * @var array<int, string>
     */
    protected array $casts = [];

    protected $table = 'sample_events';
    public $timestamps = false;

    /**
     * @var array<int, string>
     */
    protected array $goliDates = ['scheduled_at'];

    public function __construct()
    {
        $this->initializeHasGoliDates();
    }

    public function getDateFormat(): string
    {
        return 'Y-m-d H:i:s';
    }

    /**
     * @return array<string, class-string>
     */
    public function getCasts(): array
    {
        return $this->casts;
    }
}

$cast = new GoliDateCast();
$model = new SampleEvent();

assertCastRegistered($model, 'scheduled_at');

$cases = [
    'gregorian-string' => '2024-03-20 10:15:00',
    'carbon-instance' => Carbon::create(2024, 3, 21, 0, 0, 0, 'Asia/Tehran'),
    'jalali-string' => '1403-01-02 08:30:45',
    'jalali-array' => [
        'year' => 1402,
        'month' => 12,
        'day' => 29,
        'hour' => 23,
        'minute' => 59,
        'second' => 59,
    ],
    'goli-instance' => Goli::parseGoli('1403-01-05 18:00:00'),
];

foreach ($cases as $label => $input) {
    $expected = Goli::instance($input)->formatGregorian($model->getDateFormat());

    $stored = $cast->set($model, 'scheduled_at', $input, ['scheduled_at' => null]);

    if ($stored !== $expected) {
        throw new RuntimeException(sprintf(
            'Case "%s" failed when storing. Expected %s, got %s',
            $label,
            $expected,
            (string) $stored
        ));
    }

    $retrieved = $cast->get($model, 'scheduled_at', $stored, ['scheduled_at' => $stored]);

    if (! $retrieved instanceof Goli) {
        throw new RuntimeException(sprintf('Case "%s" did not return a Goli instance.', $label));
    }

    $roundTrip = $retrieved->formatGregorian($model->getDateFormat());

    if ($roundTrip !== $expected) {
        throw new RuntimeException(sprintf(
            'Case "%s" failed round trip. Expected %s, got %s',
            $label,
            $expected,
            $roundTrip
        ));
    }
}

if ($cast->set($model, 'scheduled_at', null, []) !== null) {
    throw new RuntimeException('Null values should persist as null.');
}

if ($cast->get($model, 'scheduled_at', null, []) !== null) {
    throw new RuntimeException('Null database values should return null.');
}

$model->addGoliDateCast('starts_at');
assertCastRegistered($model, 'starts_at');

echo "All Goli date cast assertions passed." . PHP_EOL;

function assertCastRegistered(object $model, string $key): void
{
    $casts = $model->getCasts();

    if (($casts[$key] ?? null) !== GoliDateCast::class) {
        throw new RuntimeException(sprintf('Cast for "%s" is missing on %s.', $key, $model::class));
    }
}
