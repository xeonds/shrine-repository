use Exception;

define('DS', DIRECTORY_SEPARATOR);

class Repo
{

    /**
     * @var string
     */
    public static $bin = '/usr/bin/git';

    /**
     * @var array
     */
    protected $envopts = [];
    /**
     * @var
     */
    private $repoPath;
    /**
     * @var bool
     */
    private $bare;


    /**
     * Repo constructor.
     * @param $repoPath
     * @throws Exception
     */
    public function __construct($repoPath)
    {
        if (!realpath($repoPath)) {
            throw new Exception('"' . $repoPath . '" does not exist');
        }
        $this->repoPath = $repoPath;
        $this->bare     = is_dir($repoPath . DS . '.git');
    }

    /**
     * 版本库是否为空版本库
     * @return bool
     */
    public function bare()
    {
        return $this->bare;
    }


    /**
     * 仓库初始化
     * @param bool $force
     * @throws Exception
     */
    public function init($force = false)
    {
        if ($force && $this->bare()) {
            $this->runCommand('rm -rf .git');
        }
        $this->run('init');
    }

    /**
     * Runs a 'git status' call
     *
     * Accept a convert to HTML bool
     *
     * @access public
     * @param bool  return string with <br />
     * @return string
     * @throws Exception
     */
    public function status($html = false)
    {
        $msg = $this->run("status");
        if ($html == true) {
            $msg = str_replace("\n", "<br />", $msg);
        }
        return $msg;
    }

    /**
     * Runs a `git add` call
     *
     * Accepts a list of files to add
     *
     * @access  public
     * @param mixed   files to add
     * @return  string
     * @throws Exception
     */
    public function add($files = "*")
    {
        if (is_array($files)) {
            $files = '"' . implode('" "', $files) . '"';
        }
        return $this->run("add $files -v");
    }

    /**
     * Runs a `git rm` call
     *
     * Accepts a list of files to remove
     *
     * @access  public
     * @param string $files
     * @param Boolean  use the --cached flag?
     * @return  string
     * @throws Exception
     */
    public function rm($files = "*", $cached = false)
    {
        if (is_array($files)) {
            $files = '"' . implode('" "', $files) . '"';
        }
        return $this->run("rm " . ($cached ? '--cached ' : '') . $files);
    }

    /**
     * Runs a `git commit` call
     *
     * Accepts a commit message string
     *
     * @access  public
     * @param string  commit message
     * @param boolean  should all files be committed automatically (-a flag)
     * @return  string
     * @throws Exception
     */
    public function commit($message = "", $commitAll = true)
    {
        $flags = $commitAll ? '-av' : '-v';
        return $this->run("commit " . $flags . " -m " . escapeshellarg($message));
    }

    /**
     * Runs a `git clone` call to clone the current repository
     * into a different directory
     *
     * Accepts a target directory
     *
     * @access  public
     * @param string  target directory
     * @return  string
     * @throws Exception
     */
    public function cloneTo($target)
    {
        return $this->run("clone --local " . $this->repoPath . " $target");
    }

    /**
     * Runs a `git clone` call to clone a different repository
     * into the current repository
     *
     * Accepts a source directory
     *
     * @access  public
     * @param string  source directory
     * @return  string
     * @throws Exception
     */
    public function cloneFrom($source)
    {
        return $this->run("clone --local $source " . $this->repoPath);
    }

    /**
     * Runs a `git clone` call to clone a remote repository
     * into the current repository
     *
     * Accepts a source url
     *
     * @access  public
     * @param $source
     * @param $reference
     * @return  string
     * @throws Exception
     */
    public function cloneRemote($source, $reference)
    {
        return $this->run("clone $reference $source " . $this->repoPath);
    }


    /**
     * Runs a `git clean` call
     *
     * Accepts a remove directories flag
     *
     * @access  public
     * @param bool $dirs
     * @param bool $force
     * @return  string
     * @throws Exception
     */
    public function clean($dirs = false, $force = false)
    {
        return $this->run("clean" . (($force) ? " -f" : "") . (($dirs) ? " -d" : ""));
    }

    /**
     * Runs a `git branch` call
     *
     * Accepts a name for the branch
     *
     * @access  public
     * @param string  branch name
     * @return  string
     * @throws Exception
     */
    public function createBranch($branch)
    {
        return $this->run("branch " . escapeshellarg($branch));
    }


    /**
     * Runs a `git branch -[d|D]` call
     *
     * Accepts a name for the branch
     *
     * @access  public
     * @param string  branch name
     * @param bool $force
     * @return  string
     * @throws Exception
     */
    public function deleteBranch($branch, $force = false)
    {
        return $this->run("branch " . (($force) ? '-D' : '-d') . " $branch");
    }

    /**
     * Runs a `git branch` call
     *
     * @access  public
     * @param bool    keep asterisk mark on active branch
     * @return  array
     * @throws Exception
     */
    public function listBranches($keep_asterisk = false)
    {
        $branchArray = explode("\n", $this->run("branch"));
        foreach ($branchArray as $i => &$branch) {
            $branch = trim($branch);
            if (!$keep_asterisk) {
                $branch = str_replace("* ", "", $branch);
            }
            if ($branch == "") {
                unset($branchArray[$i]);
            }
        }
        return $branchArray;
    }

    /**
     * Lists remote branches (using `git branch -r`).
     *
     * Also strips out the HEAD reference (e.g. "origin/HEAD -> origin/master").
     *
     * @access  public
     * @return  array
     * @throws Exception
     */
    public function listRemoteBranches()
    {
        $branchArray = explode("\n", $this->run("branch -r"));
        foreach ($branchArray as $i => &$branch) {
            $branch = trim($branch);
            if ($branch == "" || strpos($branch, 'HEAD -> ') !== false) {
                unset($branchArray[$i]);
            }
        }
        return $branchArray;
    }

    /**
     * Returns name of active branch
     *
     * @access  public
     * @param bool    keep asterisk mark on branch name
     * @return  string
     * @throws Exception
     */
    public function activeBranch($keep_asterisk = false)
    {
        $branchArray   = $this->listBranches(true);
        $active_branch = preg_grep("/^\*/", $branchArray);
        reset($active_branch);
        if ($keep_asterisk) {
            return current($active_branch);
        } else {
            return str_replace("* ", "", current($active_branch));
        }
    }

    /**
     * Runs a `git checkout` call
     *
     * Accepts a name for the branch
     *
     * @access  public
     * @param string  branch name
     * @return  string
     * @throws Exception
     */
    public function checkout($branch)
    {
        return $this->run("checkout " . escapeshellarg($branch));
    }


    /**
     * Runs a `git merge` call
     *
     * Accepts a name for the branch to be merged
     *
     * @access  public
     * @param string $branch
     * @return  string
     * @throws Exception
     */
    public function merge($branch)
    {
        return $this->run("merge " . escapeshellarg($branch) . " --no-ff");
    }

    /**
     * @param $command
     * @return bool|string
     * @throws Exception
     */
    public function run($command)
    {
        return $this->runCommand(static::$bin . " " . $command);
    }

    /**
     * Runs a git fetch on the current branch
     *
     * @access  public
     * @return  string
     * @throws Exception
     */
    public function fetch()
    {
        return $this->run("fetch");
    }

    /**
     * Add a new tag on the current position
     *
     * Accepts the name for the tag and the message
     *
     * @param string $tag
     * @param string $message
     * @return string
     * @throws Exception
     */
    public function addTag($tag, $message = null)
    {
        if (is_null($message)) {
            $message = $tag;
        }
        return $this->run("tag -a $tag -m " . escapeshellarg($message));
    }

    /**
     * List all the available repository tags.
     *
     * Optionally, accept a shell wildcard pattern and return only tags matching it.
     *
     * @access    public
     * @param string $pattern Shell wildcard pattern to match tags against.
     * @return    array                Available repository tags.
     * @throws Exception
     */
    public function listTags($pattern = null)
    {
        $tagArray = explode("\n", $this->run("tag -l $pattern"));
        foreach ($tagArray as $i => &$tag) {
            $tag = trim($tag);
            if (empty($tag)) {
                unset($tagArray[$i]);
            }
        }

        return $tagArray;
    }

    /**
     * Push specific branch (or all branches) to a remote
     *
     * Accepts the name of the remote and local branch.
     * If omitted, the command will be "git push", and therefore will take
     * on the behavior of your "push.defualt" configuration setting.
     *
     * @param string $remote
     * @param string $branch
     * @return string
     * @throws Exception
     */
    public function push($remote = "", $branch = "")
    {
        //--tags removed since this was preventing branches from being pushed (only tags were)
        return $this->run("push $remote $branch");
    }

    /**
     * Pull specific branch from remote
     *
     * Accepts the name of the remote and local branch.
     * If omitted, the command will be "git pull", and therefore will take on the
     * behavior as-configured in your clone / environment.
     *
     * @param string $remote
     * @param string $branch
     * @return string
     * @throws Exception
     */
    public function pull($remote = "", $branch = "")
    {
        return $this->run("pull $remote $branch");
    }

    /**
     * List log entries.
     *
     * @param string $format
     * @param bool $fullDiff
     * @param null $filepath
     * @param bool $follow
     * @return string
     * @throws Exception
     */
    public function log($format = null, $fullDiff = false, $filepath = null, $follow = false)
    {
        $diff = "";
        if ($fullDiff) {
            $diff = "--full-diff -p ";
        }
        if ($follow) {
            // Can't use full-diff with follow
            $diff = "--follow -- ";
        }
        if ($format === null) {
            return $this->run('log ' . $diff . $filepath);
        } else {
            return $this->run('log --pretty=format:"' . $format . '" ' . $diff . $filepath);
        }
    }

    /**
     * @param $command
     * @return bool|string
     * @throws Exception
     */
    public function runCommand($command)
    {
        $des   = [
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];
        $pipes = [];
        if (count($_ENV) === 0) {
            $env = NULL;
            foreach ($this->envopts as $k => $v) {
                putenv(sprintf("%s=%s", $k, $v));
            }
        } else {
            $env = array_merge($_ENV, $this->envopts);
        }
        $cwd      = $this->repoPath;
        $resource = proc_open($command, $des, $pipes, $cwd, $env);
        $stdout   = stream_get_contents($pipes[1]);
        $stderr   = stream_get_contents($pipes[2]);
        foreach ($pipes as $pipe) {
            fclose($pipe);
        }
        $status = trim(proc_close($resource));
        if ($status) {
            throw new Exception($stderr . "\n" . $stdout);
        }
        return $stdout;
    }
}