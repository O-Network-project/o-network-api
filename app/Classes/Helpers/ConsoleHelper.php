<?php

namespace App\Classes\Helpers;

use \Illuminate\Console\Command;

class ConsoleHelper
{
    /**
     * Prompts the user to select an option using single-character shortcuts.
     *
     * The first option is used as default if the user presses Enter directly.
     * The prompt will repeat until the user enters a valid choice.
     *
     * @param Command $command Laravel console instance
     * @param string $prompt Prompt displayed to user, without values indicator
     * @param array<string,string> $options Key-value pairs where keys are single characters and values are option labels. Used to build values indicator automatically.
     * @return string Selected option value
     *
     * @example
     * $size = promptForOption($this->command, 'Choose size', ['s' => 'small', 'l' => 'large']);
     * // Displays: "Choose size [S/b]"
     * // Returns: "small" when choosing "s" and "large" when choosing "l"
     */
    public static function promptForOption(Command $command, string $prompt, array $options): string
    {
        $options = array_change_key_case($options, CASE_LOWER);
        $optionKeys = array_keys($options);

        $formattedKeys = $optionKeys;
        $formattedKeys[0] = strtoupper($formattedKeys[0]);
        $prompt .= " [".implode('/', $formattedKeys)."]";

        $validInputs = ['', ...$optionKeys];

        do {
            $reply = strtolower($command->ask($prompt));
        } while (!in_array($reply, $validInputs));

        return $options[$reply ?: $optionKeys[0]];
    }
}
