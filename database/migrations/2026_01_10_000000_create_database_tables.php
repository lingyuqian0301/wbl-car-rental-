<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Create persondetails table first (referenced by many tables)
        Schema::create('persondetails', function (Blueprint $table) {
            $table->string('ic_no', 20)->primary();
            $table->string('fullname', 100)->nullable();
        });

        // Create user table
        Schema::create('user', function (Blueprint $table) {
            $table->id('userID');
            $table->string('username', 50)->nullable();
            $table->string('password', 255)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('name', 100)->nullable();
            $table->dateTime('lastLogin')->nullable();
            $table->dateTime('dateRegistered')->nullable();
            $table->date('DOB')->nullable();
            $table->integer('age')->nullable();
            $table->boolean('isActive')->nullable();
        });

        // Create ownercar table
        Schema::create('ownercar', function (Blueprint $table) {
            $table->id('ownerID');
            $table->string('ic_no', 20)->nullable();
            $table->string('contact_number', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('bankname', 50)->nullable();
            $table->string('bank_acc_number', 30)->nullable();
            $table->date('registration_date')->nullable();
            $table->date('license_expirydate')->nullable();
            $table->string('license_img', 255)->nullable();
            $table->date('leasing_due_date')->nullable();
            $table->decimal('leasing_price', 10, 2)->nullable();
            $table->boolean('isActive')->default(true);
            $table->integer('leasing_end_month')->nullable();
            $table->integer('leasing_end_year')->nullable();
            
            $table->foreign('ic_no')->references('ic_no')->on('persondetails');
        });

        // Create vehicle table
        Schema::create('vehicle', function (Blueprint $table) {
            $table->id('vehicleID');
            $table->string('plate_number', 20)->nullable();
            $table->string('availability_status', 30)->nullable();
            $table->date('created_date')->nullable();
            $table->string('vehicle_brand', 50)->nullable();
            $table->string('vehicle_model', 50)->nullable();
            $table->integer('manufacturing_year')->nullable();
            $table->string('color', 30)->nullable();
            $table->decimal('engineCapacity', 5, 2)->nullable();
            $table->string('vehicleType', 30)->nullable();
            $table->decimal('rental_price', 10, 2)->nullable();
            $table->boolean('isActive')->nullable();
            $table->unsignedBigInteger('ownerID')->nullable();
            
            $table->foreign('ownerID')->references('ownerID')->on('ownercar');
        });

        // Create customer table
        Schema::create('customer', function (Blueprint $table) {
            $table->id('customerID');
            $table->string('phone_number', 20)->nullable();
            $table->string('address', 255)->nullable();
            $table->string('customer_license', 50);
            $table->string('customer_license_img', 255)->nullable();
            $table->string('emergency_contact', 50)->nullable();
            $table->string('default_bank_name', 50)->nullable();
            $table->string('default_account_no', 30)->nullable();
            $table->integer('booking_times')->default(0);
            $table->unsignedBigInteger('userID')->nullable();
            
            $table->foreign('userID')->references('userID')->on('user');
        });

        // Create admin table
        Schema::create('admin', function (Blueprint $table) {
            $table->id('adminID');
            $table->string('ic_no', 20)->nullable();
            $table->unsignedBigInteger('userID')->nullable();
            
            $table->foreign('userID')->references('userID')->on('user');
            $table->foreign('ic_no')->references('ic_no')->on('persondetails');
        });

        // Create staff table
        Schema::create('staff', function (Blueprint $table) {
            $table->id('staffID');
            $table->string('ic_no', 20)->nullable();
            $table->unsignedBigInteger('userID')->nullable();
            
            $table->foreign('userID')->references('userID')->on('user');
            $table->foreign('ic_no')->references('ic_no')->on('persondetails');
        });

        // Create booking table
        Schema::create('booking', function (Blueprint $table) {
            $table->id('bookingID');
            $table->dateTime('lastUpdateDate')->nullable();
            $table->dateTime('rental_start_date')->nullable();
            $table->dateTime('rental_end_date')->nullable();
            $table->integer('duration')->nullable();
            $table->decimal('deposit_amount', 10, 2)->nullable();
            $table->decimal('rental_amount', 10, 2)->nullable();
            $table->string('pickup_point', 100)->nullable();
            $table->string('return_point', 100)->nullable();
            $table->string('addOns_item', 100)->nullable();
            $table->string('booking_status', 30)->nullable();
            $table->unsignedBigInteger('staff_served')->nullable();
            $table->unsignedBigInteger('customerID')->nullable();
            $table->unsignedBigInteger('vehicleID')->nullable();
            $table->timestamp('created_at')->useCurrent();
            
            $table->foreign('customerID')->references('customerID')->on('customer');
            $table->foreign('vehicleID')->references('vehicleID')->on('vehicle');
            $table->foreign('staff_served')->references('staffID')->on('staff');
        });

        // Create studentdetails table
        Schema::create('studentdetails', function (Blueprint $table) {
            $table->string('matric_number', 30)->primary();
            $table->string('college', 50)->nullable();
            $table->string('faculty', 50)->nullable();
            $table->string('programme', 50)->nullable();
            $table->integer('yearOfStudy')->nullable();
        });

        // Create staffdetails table
        Schema::create('staffdetails', function (Blueprint $table) {
            $table->string('staff_number', 30)->primary();
            $table->string('position', 50)->nullable();
            $table->string('college', 50)->nullable();
        });

        // Create local table
        Schema::create('local', function (Blueprint $table) {
            $table->unsignedBigInteger('customerID')->primary();
            $table->string('ic_no', 20)->nullable();
            $table->string('ic_img', 255);
            $table->string('stateOfOrigin', 50)->nullable();
            
            $table->foreign('customerID')->references('customerID')->on('customer');
            $table->foreign('ic_no')->references('ic_no')->on('persondetails');
        });

        // Create international table
        Schema::create('international', function (Blueprint $table) {
            $table->unsignedBigInteger('customerID')->primary();
            $table->string('passport_no', 30)->nullable();
            $table->string('passport_img', 255)->nullable();
            $table->string('countryOfOrigin', 50)->nullable();
            
            $table->foreign('customerID')->references('customerID')->on('customer');
        });

        // Create localstudent table
        Schema::create('localstudent', function (Blueprint $table) {
            $table->unsignedBigInteger('customerID')->primary();
            $table->string('matric_number', 30)->nullable();
            
            $table->foreign('customerID')->references('customerID')->on('local');
            $table->foreign('matric_number')->references('matric_number')->on('studentdetails');
        });

        // Create local_utmstaff table
        Schema::create('local_utmstaff', function (Blueprint $table) {
            $table->unsignedBigInteger('customerID')->primary();
            $table->string('staff_number', 30)->nullable();
            
            $table->foreign('customerID')->references('customerID')->on('local');
            $table->foreign('staff_number')->references('staff_number')->on('staffdetails');
        });

        // Create internationalstudent table
        Schema::create('internationalstudent', function (Blueprint $table) {
            $table->unsignedBigInteger('customerID')->primary();
            $table->string('matric_number', 30)->nullable();
            
            $table->foreign('customerID')->references('customerID')->on('international');
            $table->foreign('matric_number')->references('matric_number')->on('studentdetails');
        });

        // Create international_utmstaff table
        Schema::create('international_utmstaff', function (Blueprint $table) {
            $table->unsignedBigInteger('customerID')->primary();
            $table->string('staff_number', 30)->nullable();
            
            $table->foreign('customerID')->references('customerID')->on('international');
            $table->foreign('staff_number')->references('staff_number')->on('staffdetails');
        });

        // Create car table
        Schema::create('car', function (Blueprint $table) {
            $table->unsignedBigInteger('vehicleID')->primary();
            $table->unsignedBigInteger('ownerID')->nullable();
            $table->integer('seating_capacity')->nullable();
            $table->string('transmission', 20)->nullable();
            $table->string('model', 50)->nullable();
            $table->string('car_type', 30)->nullable();
            
            $table->foreign('vehicleID')->references('vehicleID')->on('vehicle');
            $table->foreign('ownerID')->references('ownerID')->on('ownercar');
        });

        // Create motorcycle table
        Schema::create('motorcycle', function (Blueprint $table) {
            $table->unsignedBigInteger('vehicleID')->primary();
            $table->string('motor_type', 50)->nullable();
            
            $table->foreign('vehicleID')->references('vehicleID')->on('vehicle');
        });

        // Create vehicledocument table
        Schema::create('vehicledocument', function (Blueprint $table) {
            $table->id('documentID');
            $table->date('upload_date')->nullable();
            $table->date('verification_date')->nullable();
            $table->string('fileURL', 255)->nullable();
            $table->string('document_type', 50)->nullable();
            $table->string('leasing_document_url', 255)->nullable();
            $table->unsignedBigInteger('vehicleID')->nullable();
            
            $table->foreign('vehicleID')->references('vehicleID')->on('vehicle');
        });

        // Create additionalcharges table
        Schema::create('additionalcharges', function (Blueprint $table) {
            $table->id('chargeID');
            $table->decimal('addOns_charge', 10, 2)->nullable();
            $table->decimal('late_return_fee', 10, 2)->nullable();
            $table->decimal('damage_fee', 10, 2)->nullable();
            $table->decimal('total_extra_charge', 10, 2)->nullable();
            $table->unsignedBigInteger('bookingID')->nullable();
            
            $table->foreign('bookingID')->references('bookingID')->on('booking');
        });

        // Create browsehistory table
        Schema::create('browsehistory', function (Blueprint $table) {
            $table->unsignedBigInteger('vehicleID');
            $table->unsignedBigInteger('customerID');
            
            $table->primary(['vehicleID', 'customerID']);
            $table->foreign('vehicleID')->references('vehicleID')->on('vehicle');
            $table->foreign('customerID')->references('customerID')->on('customer');
        });

        // Create car_img table
        Schema::create('car_img', function (Blueprint $table) {
            $table->id('imgID');
            $table->string('imageType', 50)->nullable();
            $table->string('img_description', 255)->nullable();
            $table->unsignedBigInteger('documentID')->nullable();
            
            $table->foreign('documentID')->references('documentID')->on('vehicledocument');
        });

        // Create grantdoc table
        Schema::create('grantdoc', function (Blueprint $table) {
            $table->id('grantID');
            $table->string('grantNo', 50)->nullable();
            $table->string('grantType', 50)->nullable();
            $table->date('grant_expirydate')->nullable();
            $table->unsignedBigInteger('documentID')->nullable();
            
            $table->foreign('documentID')->references('documentID')->on('vehicledocument');
        });

        // Create insurance table
        Schema::create('insurance', function (Blueprint $table) {
            $table->id('ins_ID');
            $table->string('ins_company', 50)->nullable();
            $table->string('ins_coverageType', 50)->nullable();
            $table->date('ins_expirydate')->nullable();
            $table->string('policyno', 50)->nullable();
            $table->unsignedBigInteger('documentID')->nullable();
            
            $table->foreign('documentID')->references('documentID')->on('vehicledocument');
        });

        // Create invoice table
        Schema::create('invoice', function (Blueprint $table) {
            $table->id('invoiceID');
            $table->date('issue_date')->nullable();
            $table->string('invoice_number', 50)->nullable();
            $table->decimal('totalAmount', 10, 2)->nullable();
            $table->unsignedBigInteger('bookingID')->nullable();
            
            $table->foreign('bookingID')->references('bookingID')->on('booking');
        });

        // Create loyaltycard table
        Schema::create('loyaltycard', function (Blueprint $table) {
            $table->id('loyaltyCardID');
            $table->date('loyalty_last_updated')->nullable();
            $table->integer('total_stamps')->nullable();
            $table->unsignedBigInteger('customerID')->nullable();
            
            $table->foreign('customerID')->references('customerID')->on('customer');
        });

        // Create payment table
        Schema::create('payment', function (Blueprint $table) {
            $table->id('paymentID');
            $table->string('payment_bank_name', 50)->nullable();
            $table->string('payment_bank_account_no', 30)->nullable();
            $table->dateTime('payment_date')->nullable();
            $table->decimal('total_amount', 10, 2)->nullable();
            $table->string('payment_status', 30)->nullable();
            $table->string('transaction_reference', 50)->nullable();
            $table->boolean('isPayment_complete')->nullable();
            $table->boolean('payment_isVerify')->nullable();
            $table->dateTime('latest_Update_Date_Time')->nullable();
            $table->unsignedBigInteger('bookingID')->nullable();
            $table->string('proof_of_payment', 255)->nullable();
            
            $table->foreign('bookingID')->references('bookingID')->on('booking');
        });

        // Create review table
        Schema::create('review', function (Blueprint $table) {
            $table->id('reviewID');
            $table->integer('rating')->nullable();
            $table->text('comment')->nullable();
            $table->date('review_date')->nullable();
            $table->unsignedBigInteger('bookingID')->nullable();
            
            $table->foreign('bookingID')->references('bookingID')->on('booking');
        });

        // Create roadtax table
        Schema::create('roadtax', function (Blueprint $table) {
            $table->id('roadtax_ID');
            $table->string('roadtax_certificationNo', 50)->nullable();
            $table->date('roadtax_expirydate')->nullable();
            $table->unsignedBigInteger('documentID')->nullable();
            
            $table->foreign('documentID')->references('documentID')->on('vehicledocument');
        });

        // Create runner table
        Schema::create('runner', function (Blueprint $table) {
            $table->unsignedBigInteger('staffID')->primary();
            $table->decimal('commission', 10, 2)->nullable();
            
            $table->foreign('staffID')->references('staffID')->on('staff');
        });

        // Create staffit table
        Schema::create('staffit', function (Blueprint $table) {
            $table->unsignedBigInteger('staffID')->primary();
            $table->decimal('salary', 10, 2)->nullable();
            
            $table->foreign('staffID')->references('staffID')->on('staff');
        });

        // Create systemlog table
        Schema::create('systemlog', function (Blueprint $table) {
            $table->id('logID');
            $table->string('userType', 30)->nullable();
            $table->text('action')->nullable();
            $table->dateTime('timestamp')->nullable();
            $table->unsignedBigInteger('userID')->nullable();
            
            $table->foreign('userID')->references('userID')->on('user');
        });

        // Create vehicleconditionform table
        Schema::create('vehicleconditionform', function (Blueprint $table) {
            $table->id('formID');
            $table->enum('form_type', ['RECEIVE', 'RETURN']);
            $table->integer('odometer_reading');
            $table->enum('fuel_level', ['EMPTY', '1/4', '1/2', '3/4', 'FULL']);
            $table->text('scratches_notes')->nullable();
            $table->dateTime('reported_dated_time');
            $table->unsignedBigInteger('bookingID');
            
            $table->foreign('bookingID')->references('bookingID')->on('booking');
        });

        // Create vehicleconditionimage table
        Schema::create('vehicleconditionimage', function (Blueprint $table) {
            $table->id('imageID');
            $table->string('image_path', 255);
            $table->dateTime('image_taken_time')->nullable();
            $table->unsignedBigInteger('formID');
            
            $table->foreign('formID')->references('formID')->on('vehicleconditionform');
        });

        // Create vehicleimageview table
        Schema::create('vehicleimageview', function (Blueprint $table) {
            $table->id('viewID');
            $table->string('view_name', 30)->unique();
            $table->string('description', 100)->nullable();
        });

        // Create vehiclemaintenance table
        Schema::create('vehiclemaintenance', function (Blueprint $table) {
            $table->id('maintenanceID');
            $table->integer('mileage')->nullable();
            $table->date('service_date')->nullable();
            $table->string('service_type', 50)->nullable();
            $table->date('next_due_date')->nullable();
            $table->decimal('cost', 10, 2)->nullable();
            $table->decimal('commission_amount', 10, 2)->default(0.00);
            $table->string('service_center', 100)->nullable();
            $table->text('description')->nullable();
            $table->string('maintenace_img', 255);
            $table->unsignedBigInteger('vehicleID')->nullable();
            $table->unsignedBigInteger('staffID')->nullable();
            
            $table->foreign('vehicleID')->references('vehicleID')->on('vehicle');
            $table->foreign('staffID')->references('staffID')->on('staff');
        });

        // Create voucher table
        Schema::create('voucher', function (Blueprint $table) {
            $table->id('voucherID');
            $table->unsignedBigInteger('loyaltyCardID')->nullable();
            $table->text('discount_type');
            $table->decimal('discount_amount', 10, 0);
            $table->boolean('voucher_isActive');
            
            $table->foreign('loyaltyCardID')->references('loyaltyCardID')->on('loyaltycard');
        });

        // Create walletaccount table
        Schema::create('walletaccount', function (Blueprint $table) {
            $table->id('walletAccountID');
            $table->decimal('wallet_balance', 10, 2)->nullable();
            $table->decimal('outstanding_amount', 10, 2)->nullable();
            $table->string('wallet_status', 20)->nullable();
            $table->dateTime('wallet_lastUpdate_Date_Time')->nullable();
            $table->unsignedBigInteger('customerID')->nullable();
            
            $table->foreign('customerID')->references('customerID')->on('customer');
        });

        // Create fuel table
        Schema::create('fuel', function (Blueprint $table) {
            $table->id('fuelID');
            $table->unsignedBigInteger('vehicleID');
            $table->date('fuel_date');
            $table->decimal('cost', 10, 2);
            $table->string('receipt_img', 255)->nullable();
            $table->unsignedBigInteger('handled_by')->nullable();
            $table->timestamps();
            
            $table->foreign('vehicleID')->references('vehicleID')->on('vehicle');
            $table->foreign('handled_by')->references('staffID')->on('staff');
        });

        // Create booking_read_status table
        Schema::create('booking_read_status', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id');
            $table->unsignedBigInteger('user_id');
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            
            $table->foreign('booking_id')->references('bookingID')->on('booking');
            $table->foreign('user_id')->references('userID')->on('user');
        });

        // Laravel framework tables
        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->mediumText('value');
            $table->integer('expiration');
        });

        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('owner');
            $table->integer('expiration');
        });

        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });

        Schema::create('item_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });

        Schema::create('job_batches', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->integer('total_jobs');
            $table->integer('pending_jobs');
            $table->integer('failed_jobs');
            $table->longText('failed_job_ids');
            $table->mediumText('options')->nullable();
            $table->integer('cancelled_at')->nullable();
            $table->integer('created_at');
            $table->integer('finished_at')->nullable();
        });

        // migrations table is created automatically by Laravel

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        // migrations table is managed by Laravel
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('item_categories');
        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('cache_locks');
        Schema::dropIfExists('cache');
        Schema::dropIfExists('booking_read_status');
        Schema::dropIfExists('fuel');
        Schema::dropIfExists('walletaccount');
        Schema::dropIfExists('voucher');
        Schema::dropIfExists('vehiclemaintenance');
        Schema::dropIfExists('vehicleimageview');
        Schema::dropIfExists('vehicleconditionimage');
        Schema::dropIfExists('vehicleconditionform');
        Schema::dropIfExists('systemlog');
        Schema::dropIfExists('staffit');
        Schema::dropIfExists('runner');
        Schema::dropIfExists('roadtax');
        Schema::dropIfExists('review');
        Schema::dropIfExists('payment');
        Schema::dropIfExists('loyaltycard');
        Schema::dropIfExists('invoice');
        Schema::dropIfExists('insurance');
        Schema::dropIfExists('grantdoc');
        Schema::dropIfExists('car_img');
        Schema::dropIfExists('browsehistory');
        Schema::dropIfExists('additionalcharges');
        Schema::dropIfExists('vehicledocument');
        Schema::dropIfExists('motorcycle');
        Schema::dropIfExists('car');
        Schema::dropIfExists('international_utmstaff');
        Schema::dropIfExists('internationalstudent');
        Schema::dropIfExists('local_utmstaff');
        Schema::dropIfExists('localstudent');
        Schema::dropIfExists('international');
        Schema::dropIfExists('local');
        Schema::dropIfExists('staffdetails');
        Schema::dropIfExists('studentdetails');
        Schema::dropIfExists('booking');
        Schema::dropIfExists('staff');
        Schema::dropIfExists('admin');
        Schema::dropIfExists('customer');
        Schema::dropIfExists('vehicle');
        Schema::dropIfExists('ownercar');
        Schema::dropIfExists('user');
        Schema::dropIfExists('persondetails');
    }
};