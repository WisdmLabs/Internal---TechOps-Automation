<?php
namespace TechOpsContentSync;

use CzProject\GitPhp\Git;
use CzProject\GitPhp\GitException;
use CzProject\GitPhp\IRunner;
use CzProject\GitPhp\Runners\CliRunner;

class Git_Handler {
    private $temp_dir;
    private $git;
    private $runner;

    public function __construct() {
        $this->temp_dir = wp_upload_dir()['basedir'] . '/techops-content-sync/temp';
        if (!file_exists($this->temp_dir)) {
            wp_mkdir_p($this->temp_dir);
        }
        $this->runner = new CliRunner();
        $this->git = new Git($this->runner);
    }

    /**
     * Download a specific folder from a Git repository as a zip file
     *
     * @param string $repo_url Git repository URL
     * @param string $folder_path Path to the folder within the repository
     * @param string|null $branch_or_tag Specific branch or tag to clone
     * @return array|WP_Error Success response with zip path or WP_Error on failure
     */
    public function download_folder_as_zip($repo_url, $folder_path, $branch_or_tag = null) {
        try {
            // Validate inputs
            if (empty($repo_url) || empty($folder_path)) {
                return new \WP_Error('invalid_input', 'Repository URL and folder path are required.');
            }

            // Create a unique temporary directory for this operation
            $temp_repo_dir = $this->temp_dir . '/' . uniqid('repo_');
            wp_mkdir_p($temp_repo_dir);

            // Clone the repository
            try {
                $repo = $this->git->cloneRepository($repo_url, $temp_repo_dir);
                
                if ($branch_or_tag) {
                    $repo->checkout($branch_or_tag);
                }
            } catch (GitException $e) {
                throw new \Exception('Failed to clone repository: ' . $e->getMessage());
            }

            // Ensure the folder exists in the repository
            $target_folder = $temp_repo_dir . '/' . trim($folder_path, '/');
            if (!file_exists($target_folder)) {
                throw new \Exception('Specified folder does not exist in the repository');
            }

            // Create zip file
            $zip_filename = 'repo_' . uniqid() . '.zip';
            $zip_path = $this->temp_dir . '/' . $zip_filename;
            
            $zip = new \ZipArchive();
            if ($zip->open($zip_path, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
                throw new \Exception('Failed to create zip file');
            }

            // Add folder contents to zip
            $this->add_folder_to_zip($target_folder, $zip, basename($folder_path));

            $zip->close();

            // Clean up cloned repository
            $this->remove_directory($temp_repo_dir);

            return array(
                'success' => true,
                'zip_path' => $zip_path,
                'zip_url' => str_replace(ABSPATH, site_url('/'), $zip_path)
            );

        } catch (\Exception $e) {
            // Clean up on error
            if (isset($temp_repo_dir) && file_exists($temp_repo_dir)) {
                $this->remove_directory($temp_repo_dir);
            }
            if (isset($zip_path) && file_exists($zip_path)) {
                unlink($zip_path);
            }
            return new \WP_Error('git_error', $e->getMessage());
        }
    }

    /**
     * Get available branches and tags from a repository
     *
     * @param string $repo_url Git repository URL
     * @return array|WP_Error Array of branches and tags or WP_Error on failure
     */
    public function get_branches_and_tags($repo_url) {
        try {
            $temp_repo_dir = $this->temp_dir . '/' . uniqid('repo_');
            wp_mkdir_p($temp_repo_dir);

            try {
                $repo = $this->git->cloneRepository($repo_url, $temp_repo_dir);
                
                $branches = $repo->getBranches();
                $tags = $repo->getTags();
                
                $this->remove_directory($temp_repo_dir);
                
                return array(
                    'branches' => $branches,
                    'tags' => $tags
                );
            } catch (GitException $e) {
                throw new \Exception('Failed to fetch repository information: ' . $e->getMessage());
            }
        } catch (\Exception $e) {
            if (isset($temp_repo_dir) && file_exists($temp_repo_dir)) {
                $this->remove_directory($temp_repo_dir);
            }
            return new \WP_Error('git_error', $e->getMessage());
        }
    }

    /**
     * Recursively add folder contents to zip
     */
    private function add_folder_to_zip($folder, $zip, $relative_path = '') {
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($folder),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            if (!$file->isDir()) {
                $file_path = $file->getRealPath();
                $relative_file_path = $relative_path . '/' . substr($file_path, strlen($folder) + 1);
                $zip->addFile($file_path, $relative_file_path);
            }
        }
    }

    /**
     * Recursively remove a directory
     */
    private function remove_directory($dir) {
        if (!file_exists($dir)) {
            return true;
        }

        if (!is_dir($dir)) {
            return unlink($dir);
        }

        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }

            if (!$this->remove_directory($dir . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }
        }

        return rmdir($dir);
    }
} 