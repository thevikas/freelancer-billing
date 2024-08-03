<?php

use yii\db\Migration;

/**
 * Class m240803_041626_create_uploads
 */
class m240803_041626_create_uploads extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        /*
        CREATE TABLE `uploads` (
 `id_upload` int(11) NOT NULL AUTO_INCREMENT,
 `id_datasource` int(11) NOT NULL,
 `id_user` int(11) NOT NULL,
 `filename` varchar(255) NOT NULL,
 `filepath` varchar(255) NOT NULL,
 `filetype` varchar(255) NOT NULL,
 `created` datetime NOT NULL,
 `url` varchar(255) NOT NULL,
 `status` enum('NEW','PROCESSING','PROCESSED','ERROR') DEFAULT 'NEW',
 PRIMARY KEY (`id_upload`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8
        */
        $this->createTable('uploads', [
            'id_upload' => $this->primaryKey(),
            'id_invoice' => $this->integer()->notNull(),
            'id_user' => $this->integer()->notNull(),
            'filename' => $this->string(255)->notNull(),
            'filepath' => $this->string(255)->notNull(),
            'filetype' => $this->string(255)->notNull(),
            'created' => $this->dateTime()->notNull(),
            'url_json' => $this->string(255)->notNull(),
            'status' => "enum('NEW','PROCESSING','PROCESSED','ERROR') DEFAULT 'NEW'",
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('uploads');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m240803_041626_create_uploads cannot be reverted.\n";

        return false;
    }
    */
}
