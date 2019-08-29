<?php

namespace Rumur\Pimpled\Routing\Database;

use Rumur\Pimpled\Database\Migration;

class DeleteRoutesHash extends Migration
{
    public function up()
    {
        // ... nothing todo here
    }

    public function down()
    {
        delete_option('pmld_routes_hash');
    }
}
