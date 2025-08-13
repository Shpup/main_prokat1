<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompaniesTable extends Migration
{
    public function up()
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->enum('type', ['ip', 'ur', 'fl']);        // ИП / юрид. лицо / физ. лицо
            $table->string('country');                       // страна регистрации
            $table->decimal('tax_rate', 5, 2);               // ставка налогообложения %
            $table->enum('accounting_method', [
                'osn_inclusive',     // ОСН, налог внутри “Итого”
                'osn_exclusive',     // ОСН, налог сверху
                'usn_inclusive',     // УСН, налог внутри “Итого”
                'usn_exclusive',     // УСН, налог сверху
            ]);
            $table->text('comment')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('companies');
    }
}
