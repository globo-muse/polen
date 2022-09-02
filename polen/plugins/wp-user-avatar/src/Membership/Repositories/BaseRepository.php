<?php


namespace ProfilePress\Core\Membership\Repositories;

abstract class BaseRepository implements RepositoryInterface
{
    protected $table;

    public function wpdb()
    {
        global $wpdb;

        return $wpdb;
    }

    /**
     * Update a column in table.
     *
     * @param int $id
     * @param string $column
     * @param string $value
     *
     * @return false|int
     */
    public function updateColumn($id, $column, $value)
    {
        return $this->wpdb()->update(
            $this->table,
            [$column => $value],
            ['id' => $id],
            ['%s'],
            ['%d']
        );
    }

    /**
     * Retrieve a column in DB table.
     *
     * @param int $id
     * @param string $column
     *
     * @return string|null
     */
    public function retrieveColumn($id, $column)
    {
        return $this->wpdb()->get_var(
            $this->wpdb()->prepare(
                "SELECT $column FROM $this->table WHERE id = %d",
                $id
            )
        );
    }

    /**
     * @return string|null
     */
    public function record_count()
    {
        return $this->wpdb()->get_var("SELECT COUNT(*) FROM $this->table");
    }

    /**
     * @return static
     */
    public static function init()
    {
        return new static();
    }
}