<?php

namespace Pmld\Contracts\Database;

interface Migration
{
    /**
     * Run while Activation process, create tables
     */
    public function up();

    /**
     * Run while Uninstall process, delete created tables
     */
    public function down();
}
