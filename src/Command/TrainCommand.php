<?php

namespace App\Command;

use App\Game\ValueObject\GameParameters3x3;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TrainCommand extends Command
{
    protected static $defaultName = 'ai:train';

    protected function configure()
    {
        $this->setDescription('Train AI on prepared data');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $params = new GameParameters3x3();

        $layerSizes = [
            $params->getFieldWidth() * $params->getFieldHeight() * 2, // input
            60, // 1-st hidden layer
            40, // 2-nd hidden layer
            $params->getFieldWidth() * $params->getFieldHeight(), // output
        ];

        $desiredError = 0.007;
        $epochCountLimit = 100000;
        $epochsBetweenReports = 50;

        $ann = \fann_create_standard(count($layerSizes), ...$layerSizes);

        if ($ann) {
            \fann_set_activation_function_hidden($ann, FANN_SIGMOID_SYMMETRIC_STEPWISE);
            \fann_set_activation_function_output($ann, FANN_SIGMOID_SYMMETRIC_STEPWISE);

            fann_set_callback($ann, function ($ann, $p1, $p2, $p3, $p4, $epochNum) use (&$output) {
                $mse = fann_get_MSE($ann);
                $output->writeln(sprintf('MSE: %.5f, Epoch: %d', $mse, $epochNum));
                return true;
            });

            $filename = "train_data.txt";

            if (!file_exists($filename)) {
                throw new \RuntimeException('You should first run ai:prepare command');
            }

            if (\fann_train_on_file($ann, $filename, $epochCountLimit, $epochsBetweenReports, $desiredError)) {
                $output->writeln('OK');
            } else {
                $output->writeln('FAIL');
            }

            \fann_save($ann, "xo.net");

            \fann_destroy($ann);
        }

        return 0;
    }
}