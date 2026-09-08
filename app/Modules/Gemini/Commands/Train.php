<?php

declare(strict_types=1);

namespace App\Modules\Gemini\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Class Train
 *
 * Runs the AI text classification training service to generate models.
 *
 * @package App\Modules\Gemini\Commands
 * @author  Senior Developer
 * @since   1.0.0
 */
class Train extends BaseCommand
{
    /**
     * The Command's Group.
     *
     * @var string
     */
    protected $group = 'App';

    /**
     * The Command's Name.
     *
     * @var string
     */
    protected $name = 'train';

    /**
     * The Command's Description.
     *
     * @var string
     */
    protected $description = 'Runs the AI text classification training service to generate models.';

    /**
     * The Command's Usage.
     *
     * @var string
     */
    protected $usage = 'train';

    /**
     * The Command's Arguments.
     *
     * @var array<string, string>
     */
    protected $arguments = [];

    /**
     * The Command's Options.
     *
     * @var array<string, string>
     */
    protected $options = [];

    /**
     * Actually execute a command.
     *
     * @param array<string, mixed> $params
     *
     * @return int
     */
    public function run(array $params): int
    {
        CLI::write('Starting model training process...', 'yellow');

        /** @var \App\Modules\Gemini\Libraries\TrainingService $trainingService */
        $trainingService = service('trainingService');
        $result = $trainingService->train();

        CLI::write('----------------------------------------', 'light_gray');
        if ($result['success']) {
            CLI::write($result['message'], 'green');
            CLI::write('----------------------------------------', 'light_gray');

            return EXIT_SUCCESS;
        }

        CLI::error($result['message']);
        CLI::write('----------------------------------------', 'light_gray');

        return EXIT_ERROR;
    }
}
