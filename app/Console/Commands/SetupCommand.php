<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;
use Override;
use Symfony\Component\Finder\Finder;

class SetupCommand extends Command
{
    /** @var string */
    #[Override]
    protected $signature = 'setup';

    /** @var string */
    #[Override]
    protected $description = 'Set up Smarter Kit for a new project.';

    public function handle(): void
    {
        $this->info('Running setup command...');

        $isAlreadyGitRepository = is_dir(base_path('.git'));

        if ($isAlreadyGitRepository) {
            $this->warn('Warning: This directory is already a git repository - the setup command is only meant to be run in a new project.');
        }

        $repositoryUrl = Str::replaceLast('.git', '', $this->ask("What is your project's repository URL?", 'https://github.com/imliam/smarter-kit.git'));
        $repositoryName = $this->getRepositoryName($repositoryUrl);
        $projectName = $this->ask("What is your project's name?", Str::headline(Str::after($repositoryName, '/')));
        $projectOwner = $this->ask("What is your Git owner's username or organisation?", Str::before($repositoryName, '/'));
        $securityEmail = $this->ask('What is the security contact email for your project?', 'security@example.com');

        $replacements = [
            'https://github.com/imliam/smarter-kit' => $repositoryUrl,
            'imliam/smarter-kit' => $repositoryName,
            'smarter-kit' => Str::kebab($projectName),
            'imliam' => $projectOwner,
            'Smarter Kit' => $projectName,
            'security@example.com' => $securityEmail,
        ];

        foreach ($replacements as $search => $replace) {
            $this->info("Replacing '{$search}' with '{$replace}'...");
            $this->replaceInFiles($search, $replace);
        }

        if ($isAlreadyGitRepository) {
            $this->info('Skipping git initialization as this is already a git repository.');
        } else {
            $this->info('Initializing new git repository...');
            Process::run('git init '.base_path());
            Process::run('git branch -M main');
            Process::run('git add .');
            Process::run('git commit -m "Initial commit"');
            Process::run("git remote add origin {$repositoryUrl}");
            Process::run('git push -u origin main');
        }

        $this->info('Deleting setup command...');

        $this->info('To generate a logo, run the following command:');
        $this->line('    cpx imliam/logophpile');
        // unlink(app_path('Console/Commands/SetupCommand.php'));
    }

    protected function getRepositoryName(string $repositoryUrl): string
    {
        throw_unless(Str::isUrl($repositoryUrl), Exception::class, 'The provided URL is not valid.');

        $path = parse_url($repositoryUrl, PHP_URL_PATH);

        throw_if($path === null, Exception::class, 'Could not parse the URL path.');

        if (str_ends_with($path, '.git')) {
            $path = Str::replaceLast('.git', '', $path);
        }

        $path = mb_ltrim($path, '/');

        throw_unless(str_contains($path, '/'), Exception::class, 'The provided URL does not appear to be a valid git repository URL.');

        return $path;
    }

    protected function replaceInFiles(string $search, string $replace): void
    {
        $basePath = base_path();

        $finder = new Finder();
        $finder->in($basePath)
            ->ignoreDotFiles(false)
            ->ignoreVCSIgnored(true)
            ->files();

        foreach ($finder as $file) {
            if ($file->getPathname() === __FILE__) {
                continue;
            }

            $relativePath = mb_ltrim(str_replace($basePath, '', $file->getPathname()), '/');

            $contents = file_get_contents($file->getPathname());
            $newContents = str_replace($search, $replace, $contents);

            if ($contents !== $newContents) {
                file_put_contents($file->getPathname(), $newContents);
                $this->line($relativePath);
            }
        }
    }
}
