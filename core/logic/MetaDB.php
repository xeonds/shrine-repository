<?php
include_once 'core/util/AuthCode.php';

/**
 * MetaDB: A util class for meta management
 * @method createMeta($data, $id):bool Create meta with $data: array('meta'=>...(,'filePath'=>...,'fileName'=>...))
 * @method deleteMeta(string $meta)
 * @method getList()
 * @method getMeta($metaID)
 * @method modifyMeta($id, $data) This $data is same as the one createMeta() needs.
 */
class MetaDB
{
    private $dbPath = "db/meta/";

    public function createMeta(array $data, string $id = ''): bool
    {
        try
        {
            $id = $id == '' ? (new AuthCode(0))->getCode() : $id;
            mkdir($metaPath = $this->dbPath . $id . '/');
            switch ($data['meta']->get()['type'])
            {
                case 'text':
                    break;

                case 'metaArray':
                    break;

                default:
                    move_uploaded_file($data['filePath'], $metaPath . $data['fileName']);
                    break;
            }
            file_put_contents($metaPath . 'meta.json', json_encode($data['meta']->get()));
        }
        catch (Exception $e)
        {
            return false;
        }
        finally
        {
            return true;
        }
    }

    public function deleteMeta(string $meta): bool
    {
        try
        {
            while (false !== $item = readdir(opendir($metaPath = $this->dbPath . $meta . '/')))
                if ($item != '.' && $item != '..')
                    unlink($metaPath . $item);
            return true;
        }
        catch (Exception $e)
        {
            return false;
        }
    }

    public function getList()
    {
        if (!file_exists($this->dbPath))
            return [];
        $dir = opendir($this->dbPath);
        while (false !== $item = readdir($dir))
            if ($item != '.' && $item != '..' && file_exists($this->dbPath . $item . '/meta.json'))
                $data[] = array(
                    "metaId" => $item
                ) + $this->getMeta($item);

        return $data;
    }

    public function getMeta($id, $format = 'array')
    {
        $metaData = file_get_contents($this->dbPath . $id . '/meta.json');

        return $format == 'array' ? json_decode($metaData, true) : $metaData;
    }

    public function modifyMeta(string $id, array $data): bool
    {
        try
        {
            $metaPath = $this->dbPath . $id . '/';
            switch ($this->getMeta($id)['type'])
            {
                case 'text':
                    break;

                case 'metaArray':
                    break;

                default:
                    unlink(($metaPath = $this->dbPath . $id . '/') . $this->getMeta($id)['fileName']);
                    move_uploaded_file($data['filePath'], $metaPath . $data['fileName']);
                    break;
            }
            file_put_contents($metaPath . 'meta.json', json_encode($data['meta']->get()));
        }
        catch (Exception $e)
        {
            return false;
        }
        finally
        {
            return true;
        }
    }
}
