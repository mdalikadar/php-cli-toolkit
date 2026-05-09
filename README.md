# php-cli-toolkit

A zero-dependency PHP library for building command-line applications. Covers argument parsing, styled output, interactive prompts, tables, and progress bars.

**Requires PHP 8.1+**

## Installation

```bash
composer require mdalikadar/php-cli-toolkit
```

---

## Argument parsing

### Standalone parser

Register arguments and options before calling `run()`, then read values with the accessor methods.

```php
use PhpCliToolkit\Arguments\Parser;
use PhpCliToolkit\Exceptions\ValidationException;

$parser = new Parser();

$parser->registerArg('filename', 'File to process', isRequired: true);
$parser->registerArg('output',   'Output path',     isRequired: false, default: 'out.txt');

$parser->registerOption('verbose|v', 'Enable verbose mode');
$parser->registerOption('format|f',  'Output format', isRequired: false, default: 'json');

try {
    $parser->run();
} catch (ValidationException $e) {
    foreach ($e->errors() as $error) {
        echo $error . "\n";
    }
    exit(1);
}

$file    = $parser->getArg('filename');
$output  = $parser->getArg('output');
$verbose = $parser->hasOption('verbose');
$format  = $parser->getOption('format');
```

**Invocation examples:**

```bash
php script.php data.csv --format=csv -v
php script.php data.csv --verbose
```

### Option types

| Input form | Registered as | Result |
|---|---|---|
| `--format=json` | `format` | `"json"` |
| `--verbose` | `verbose` | `true` |
| `-v` | `verbose\|v` | `true` |
| `-abc` | `a`, `b`, `c` | each `true` |

### Accessor reference

```php
$parser->getArg('name');          // value or null
$parser->getOption('name');       // value or null (alias-aware)
$parser->hasArg('name');          // true if value is not null
$parser->hasOption('name');       // true if set and not false
$parser->getArgs();               // ['name' => value, ...]
$parser->getOptions();            // ['name' => value, ...]
$parser->synopsis();              // ['arguments' => [...], 'options' => [...]]
```

---

## Commands and application

For multi-command CLIs, extend `Command` and register with `Application`.

### Defining a command

```php
use PhpCliToolkit\Command\Command;
use PhpCliToolkit\Output\Text;
use PhpCliToolkit\Output\Styles\Colors;

class GreetCommand extends Command {
    public function name(): string { return 'greet'; }
    public function description(): string { return 'Greet a user by name'; }

    protected function setup(): void {
        $this->parser->registerArg('name', 'Name to greet', isRequired: true);
        $this->parser->registerOption('shout|s', 'Uppercase the output');
    }

    public function handle(): int {
        $name = $this->parser->getArg('name');
        $msg  = "Hello, {$name}!";

        if ($this->parser->hasOption('shout')) {
            $msg = strtoupper($msg);
        }

        (new Text($msg))->style([Colors::GREEN])->write();
        return 0;
    }
}
```

### Wiring up the application

```php
use PhpCliToolkit\Command\Application;

$app = new Application('my-tool', '1.0.0');
$app->register(new GreetCommand(), new OtherCommand());
$app->run();
```

**Invocation:**

```bash
php cli.php greet Alice
php cli.php greet Alice --shout
php cli.php help
```

`Application` automatically catches `ValidationException` and prints each error before exiting with code `1`. The built-in `help` / `--help` / `-h` command lists all registered commands.

---

## Styled text output

`Text` renders ANSI-styled output with automatic word-wrap to the terminal width.

### Colors and formatting

```php
use PhpCliToolkit\Output\Text;
use PhpCliToolkit\Output\Styles\Colors;
use PhpCliToolkit\Output\Styles\BgColors;
use PhpCliToolkit\Output\Styles\Formattings;

(new Text('Success!'))->style([Colors::GREEN, Formattings::BOLD])->write();
(new Text('Warning!'))->style([Colors::YELLOW])->write();
(new Text('Error!'))->style([Colors::WHITE, BgColors::RED])->write();
(new Text('Info'))->style([Colors::CYAN, Formattings::ITALIC])->write();
```

### Available styles

**`Colors`** (foreground):
`BLACK`, `RED`, `GREEN`, `YELLOW`, `BLUE`, `MAGENTA`, `CYAN`, `WHITE`
and bright variants: `BRIGHT_BLACK`, `BRIGHT_RED`, … `BRIGHT_WHITE`

**`BgColors`** (background):
`BLACK`, `RED`, `GREEN`, `YELLOW`, `BLUE`, `MAGENTA`, `CYAN`, `WHITE`
and bright variants: `BRIGHT_BLACK`, … `BRIGHT_WHITE`

**`Formattings`**:
`BOLD`, `DIM`, `ITALIC`, `UNDERLINE`, `BLINK`, `INVERT`, `HIDE`, `RESET`

### Padding and margin helpers

`padding()` and `margin()` are named-argument shortcuts over `space()` and `edgeSpace()`:

```php
(new Text('Hello'))
    ->style([Colors::WHITE, BgColors::BLUE])
    ->padding(top: 1, bottom: 1, left: 2, right: 2, style: [BgColors::BLUE])
    ->margin(top: 1, bottom: 1)
    ->write();
```

### Spacing and padding (low-level)

`space()` adds inner padding around the text. `edgeSpace()` adds outer padding that spans the full terminal width. Use the position constants `Text::TOP`, `Text::BOTTOM`, `Text::LEFT`, `Text::RIGHT`, or `Text::ALL`.

```php
(new Text('Boxed message'))
    ->style([Colors::WHITE, BgColors::BLUE])
    ->space(Text::ALL, 1, [BgColors::BLUE])
    ->edgeSpace(Text::TOP,    1, [BgColors::BRIGHT_BLACK])
    ->edgeSpace(Text::BOTTOM, 1, [BgColors::BRIGHT_BLACK])
    ->write();
```

The `space()` call signature: `space(position, count, style_array, char = ' ')`. The char can be any character, so you can draw custom borders:

```php
(new Text('Section title'))
    ->style([Colors::BRIGHT_WHITE, Formattings::BOLD])
    ->edgeSpace(Text::BOTTOM, 1, [Colors::BRIGHT_BLACK], '-')
    ->write();
```

---

## Table

Renders an ASCII table to stdout. Column widths are computed automatically.

```php
use PhpCliToolkit\Output\Table;

$table = new Table(
    headers: ['Name',  'Role',    'Status'],
    rows: [
        ['Alice', 'Admin',   'Active'],
        ['Bob',   'Editor',  'Inactive'],
        ['Carol', 'Viewer',  'Active'],
    ]
);

$table->render();
```

**Output:**

```
+-------+---------+----------+
| Name  | Role    | Status   |
+-------+---------+----------+
| Alice | Admin   | Active   |
| Bob   | Editor  | Inactive |
| Carol | Viewer  | Active   |
+-------+---------+----------+
```

`toString()` returns the table as a string without printing it.

---

## Grid

Lays out content in columns side by side. Each column is a `Cell` that handles its own word-wrap and padding.

### Basic usage — equal columns

Pass an integer to split the terminal width into N equal columns. Pass a `gap` to control spacing between them.

```php
use PhpCliToolkit\Output\Grid;
use PhpCliToolkit\Output\Styles\Colors;
use PhpCliToolkit\Output\Styles\BgColors;

(new Grid(columns: 3, gap: 2))
    ->cell('Left column content',  style: [Colors::WHITE, BgColors::BLUE])
    ->cell('Middle column content', style: [Colors::WHITE, BgColors::GREEN])
    ->cell('Right column content',  style: [Colors::WHITE, BgColors::RED])
    ->render();
```

Cells wrap automatically at the column boundary. If cells in the same row have different heights, shorter ones are padded with blank lines so all columns stay aligned.

### Explicit column widths

Pass an array of integers to control each column's exact width in characters:

```php
(new Grid(columns: [20, 40, 20], gap: 1))
    ->cell('Narrow')
    ->cell('This is the wide centre column with more room for content')
    ->cell('Narrow')
    ->render();
```

### Cell padding

Each cell has independent top / right / bottom / left padding. Use named constructor arguments on `Cell` directly, or pass them as positional args to the `cell()` shorthand:

```php
use PhpCliToolkit\Output\Cell;

(new Grid([30, 30], gap: 2))
    ->add(new Cell(
        content:       'Padded cell',
        style:         [Colors::WHITE, BgColors::BLUE],
        paddingTop:    1,
        paddingBottom: 1,
        paddingLeft:   2,
        paddingRight:  2,
        paddingStyle:  [BgColors::BLUE],
    ))
    ->cell('No padding here')
    ->render();
```

### Multiple rows

Add more cells than there are columns and the grid automatically flows into multiple rows:

```php
$headers = ['Name', 'Role', 'Status'];
$rows    = [['Alice', 'Admin', 'Active'], ['Bob', 'Editor', 'Inactive']];

$grid = new Grid([20, 20, 20], gap: 1);

foreach ($headers as $h) {
    $grid->cell($h, style: [Colors::BRIGHT_WHITE, Formattings::BOLD]);
}
foreach ($rows as $row) {
    foreach ($row as $cell) {
        $grid->cell($cell);
    }
}

$grid->render();
```

### `toString()`

Returns the rendered grid as a string without printing it, useful for composing output:

```php
$output = (new Grid([40, 40]))->cell('A')->cell('B')->toString();
```

---

## Progress bar

Renders an in-place progress bar that updates on the same line.

```php
use PhpCliToolkit\Output\ProgressBar;

$bar = new ProgressBar(total: 100);

foreach ($items as $item) {
    process($item);
    $bar->advance();
}

$bar->finish();
```

**Output (updates in place):**

```
[==============================>---------] 75% (75/100) ETA:1s
```

`advance(int $step = 1)` moves the bar forward. `finish()` snaps to 100% and writes a newline.

---

## Interactive prompts

All methods are static and read from `STDIN`.

```php
use PhpCliToolkit\Input\Prompt;

$name = Prompt::ask('What is your name?', default: 'World');
// What is your name? [World]:

$confirmed = Prompt::confirm('Are you sure?', default: false);
// Are you sure? [y/N]:

$password = Prompt::secret('Enter password');
// Enter password: (input hidden)

$color = Prompt::select('Pick a color', ['Red', 'Green', 'Blue']);
// Pick a color
//   [1] Red
//   [2] Green
//   [3] Blue
// Choice:
```

`select()` re-prompts automatically on invalid input.

---

## Error handling

All library exceptions extend `PhpCliToolkit\Exceptions\CliException`.

```php
use PhpCliToolkit\Exceptions\ValidationException;
use PhpCliToolkit\Exceptions\CliException;

try {
    $parser->run();
} catch (ValidationException $e) {
    foreach ($e->errors() as $error) {
        echo $error . "\n";
    }
    exit(1);
} catch (CliException $e) {
    echo $e->getMessage() . "\n";
    exit(1);
}
```

`ValidationException` is thrown by `Parser::parse()` and `Parser::run()` when required arguments or options are missing. `Application` catches it automatically when using the command system.

---

## Running tests

```bash
composer install
./vendor/bin/phpunit
```
