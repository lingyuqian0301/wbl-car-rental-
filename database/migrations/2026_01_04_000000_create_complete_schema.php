<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ===== USER & PERSON =====
        Schema::create('PersonDetails', function (Blueprint $table) {
            $table->string('ic_no', 20)->primary();
            $table->string('fullname', 100);
        });

        Schema::create('User', function (Blueprint $table) {
            $table->increments('userID');
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

        // ===== CUSTOMER / STAFF / ADMIN =====
        Schema::create('Customer', function (Blueprint $table) {
            $table->increments('customerID');
            $table->string('phone_number', 20)->nullable();
            $table->string('address', 255)->nullable();
            $table->string('customer_license', 50)->nullable();
            $table->string('emergency_contact', 50)->nullable();
            $table->dateTime('booking_times')->nullable();
            $table->unsignedInteger('userID')->nullable();
            $table->foreign('userID')->references('userID')->on('User');
        });

        Schema::create('Staff', function (Blueprint $table) {
            $table->increments('staffID');
            $table->string('ic_no', 20)->nullable();
            $table->unsignedInteger('userID')->nullable();
            $table->foreign('userID')->references('userID')->on('User');
            $table->foreign('ic_no')->references('ic_no')->on('PersonDetails');
        });

        Schema::create('Admin', function (Blueprint $table) {
            $table->increments('adminID');
            $table->string('ic_no', 20)->nullable();
            $table->unsignedInteger('userID')->nullable();
            $table->foreign('userID')->references('userID')->on('User');
            $table->foreign('ic_no')->references('ic_no')->on('PersonDetails');
        });

        // ===== OWNER & VEHICLE =====
        Schema::create('OwnerCar', function (Blueprint $table) {
            $table->increments('ownerID');
            $table->string('ic_no', 20)->nullable();
            $table->string('contact_number', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('bankname', 50)->nullable();
            $table->string('bank_acc_number', 30)->nullable();
            $table->date('registration_date')->nullable();
            $table->foreign('ic_no')->references('ic_no')->on('PersonDetails');
        });

        Schema::create('Vehicle', function (Blueprint $table) {
            $table->increments('vehicleID');
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
            $table->unsignedInteger('ownerID')->nullable();
            $table->foreign('ownerID')->references('ownerID')->on('OwnerCar');
        });

        Schema::create('Car', function (Blueprint $table) {
            $table->unsignedInteger('vehicleID')->primary();
            $table->integer('seating_capacity')->nullable();
            $table->string('transmission', 20)->nullable();
            $table->string('model', 50)->nullable();
            $table->string('car_type', 30)->nullable();
            $table->foreign('vehicleID')->references('vehicleID')->on('Vehicle');
        });

        Schema::create('Motorcycle', function (Blueprint $table) {
            $table->unsignedInteger('vehicleID')->primary();
            $table->string('motor_type', 50)->nullable();
            $table->foreign('vehicleID')->references('vehicleID')->on('Vehicle');
        });

        // ===== BOOKING & PAYMENT =====
        Schema::create('Booking', function (Blueprint $table) {
            $table->increments('bookingID');
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
            $table->unsignedInteger('customerID')->nullable();
            $table->unsignedInteger('vehicleID')->nullable();
            $table->foreign('customerID')->references('customerID')->on('Customer');
            $table->foreign('vehicleID')->references('vehicleID')->on('Vehicle');
        });

        Schema::create('Payment', function (Blueprint $table) {
            $table->increments('paymentID');
            $table->string('payment_bank_name', 50)->nullable();
            $table->string('payment_bank_account_no', 30)->nullable();
            $table->dateTime('payment_date')->nullable();
            $table->decimal('total_amount', 10, 2)->nullable();
            $table->string('payment_status', 30)->nullable();
            $table->string('transaction_reference', 50)->nullable();
            $table->boolean('isPayment_complete')->nullable();
            $table->boolean('payment_isVerify')->nullable();
            $table->dateTime('latest_Update_Date_Time')->nullable();
            $table->unsignedInteger('bookingID')->nullable();
            $table->foreign('bookingID')->references('bookingID')->on('Booking');
        });

        Schema::create('Invoice', function (Blueprint $table) {
            $table->increments('invoiceID');
            $table->date('issue_date')->nullable();
            $table->string('invoice_number', 50)->nullable();
            $table->decimal('totalAmount', 10, 2)->nullable();
            $table->unsignedInteger('bookingID')->nullable();
            $table->foreign('bookingID')->references('bookingID')->on('Booking');
        });

        Schema::create('AdditionalCharges', function (Blueprint $table) {
            $table->increments('chargeID');
            $table->decimal('addOns_charge', 10, 2)->nullable();
            $table->decimal('late_return_fee', 10, 2)->nullable();
            $table->decimal('damage_fee', 10, 2)->nullable();
            $table->decimal('total_extra_charge', 10, 2)->nullable();
            $table->unsignedInteger('bookingID')->nullable();
            $table->foreign('bookingID')->references('bookingID')->on('Booking');
        });

        Schema::create('Review', function (Blueprint $table) {
            $table->increments('reviewID');
            $table->integer('rating')->nullable();
            $table->longText('comment')->nullable();
            $table->date('review_date')->nullable();
            $table->unsignedInteger('bookingID')->nullable();
            $table->foreign('bookingID')->references('bookingID')->on('Booking');
        });

        // ===== MAINTENANCE =====
        Schema::create('VehicleMaintenance', function (Blueprint $table) {
            $table->increments('maintenanceID');
            $table->integer('mileage')->nullable();
            $table->date('service_date')->nullable();
            $table->string('service_type', 50)->nullable();
            $table->date('next_due_date')->nullable();
            $table->decimal('cost', 10, 2)->nullable();
            $table->string('service_center', 100)->nullable();
            $table->longText('description')->nullable();
            $table->unsignedInteger('vehicleID')->nullable();
            $table->unsignedInteger('staffID')->nullable();
            $table->foreign('vehicleID')->references('vehicleID')->on('Vehicle');
            $table->foreign('staffID')->references('staffID')->on('Staff');
        });

        // ===== DOCUMENTS =====
        Schema::create('VehicleDocument', function (Blueprint $table) {
            $table->increments('documentID');
            $table->date('upload_date')->nullable();
            $table->date('verification_date')->nullable();
            $table->string('fileURL', 255)->nullable();
            $table->unsignedInteger('vehicleID')->nullable();
            $table->foreign('vehicleID')->references('vehicleID')->on('Vehicle');
        });

        Schema::create('Insurance', function (Blueprint $table) {
            $table->increments('ins_ID');
            $table->string('ins_company', 50)->nullable();
            $table->string('ins_coverageType', 50)->nullable();
            $table->date('ins_expirydate')->nullable();
            $table->string('policyno', 50)->nullable();
            $table->unsignedInteger('documentID')->nullable();
            $table->foreign('documentID')->references('documentID')->on('VehicleDocument');
        });

        Schema::create('Roadtax', function (Blueprint $table) {
            $table->increments('roadtax_ID');
            $table->string('roadtax_certificationNo', 50)->nullable();
            $table->date('roadtax_expirydate')->nullable();
            $table->unsignedInteger('documentID')->nullable();
            $table->foreign('documentID')->references('documentID')->on('VehicleDocument');
        });

        Schema::create('GrantDoc', function (Blueprint $table) {
            $table->increments('grantID');
            $table->string('grantNo', 50)->nullable();
            $table->string('grantType', 50)->nullable();
            $table->date('grant_expirydate')->nullable();
            $table->unsignedInteger('documentID')->nullable();
            $table->foreign('documentID')->references('documentID')->on('VehicleDocument');
        });

        Schema::create('Car_Img', function (Blueprint $table) {
            $table->increments('imgID');
            $table->string('imageType', 50)->nullable();
            $table->string('img_description', 255)->nullable();
            $table->unsignedInteger('documentID')->nullable();
            $table->foreign('documentID')->references('documentID')->on('VehicleDocument');
        });

        // ===== WALLET & LOYALTY =====
        Schema::create('WalletAccount', function (Blueprint $table) {
            $table->increments('walletAccountID');
            $table->decimal('wallet_balance', 10, 2)->nullable();
            $table->decimal('outstanding_amount', 10, 2)->nullable();
            $table->string('wallet_status', 20)->nullable();
            $table->dateTime('wallet_lastUpdate_Date_Time')->nullable();
            $table->unsignedInteger('customerID')->nullable();
            $table->foreign('customerID')->references('customerID')->on('Customer');
        });

        Schema::create('LoyaltyCard', function (Blueprint $table) {
            $table->increments('loyaltyCardID');
            $table->date('loyalty_last_updated')->nullable();
            $table->integer('total_stamps')->nullable();
            $table->unsignedInteger('customerID')->nullable();
            $table->foreign('customerID')->references('customerID')->on('Customer');
        });

        // ===== LOG & BROWSE =====
        Schema::create('SystemLog', function (Blueprint $table) {
            $table->increments('logID');
            $table->string('userType', 30)->nullable();
            $table->longText('action')->nullable();
            $table->dateTime('timestamp')->nullable();
            $table->unsignedInteger('userID')->nullable();
            $table->foreign('userID')->references('userID')->on('User');
        });

        Schema::create('BrowseHistory', function (Blueprint $table) {
            $table->unsignedInteger('vehicleID');
            $table->unsignedInteger('customerID');
            $table->primary(['vehicleID', 'customerID']);
            $table->foreign('vehicleID')->references('vehicleID')->on('Vehicle');
            $table->foreign('customerID')->references('customerID')->on('Customer');
        });

        // ===== CUSTOMER SPECIALIZATION =====
        Schema::create('Local', function (Blueprint $table) {
            $table->unsignedInteger('customerID')->primary();
            $table->string('ic_no', 20)->nullable();
            $table->string('stateOfOrigin', 50)->nullable();
            $table->foreign('customerID')->references('customerID')->on('Customer');
            $table->foreign('ic_no')->references('ic_no')->on('PersonDetails');
        });

        Schema::create('International', function (Blueprint $table) {
            $table->unsignedInteger('customerID')->primary();
            $table->string('passport_no', 30)->nullable();
            $table->string('countryOfOrigin', 50)->nullable();
            $table->foreign('customerID')->references('customerID')->on('Customer');
        });

        Schema::create('StudentDetails', function (Blueprint $table) {
            $table->string('matric_number', 30)->primary();
            $table->string('college', 50)->nullable();
            $table->string('faculty', 50)->nullable();
            $table->string('programme', 50)->nullable();
            $table->integer('yearOfStudy')->nullable();
        });

        Schema::create('StaffDetails', function (Blueprint $table) {
            $table->string('staff_number', 30)->primary();
            $table->string('position', 50)->nullable();
            $table->string('college', 50)->nullable();
        });

        Schema::create('LocalStudent', function (Blueprint $table) {
            $table->unsignedInteger('customerID')->primary();
            $table->string('matric_number', 30)->nullable();
            $table->foreign('customerID')->references('customerID')->on('Local');
            $table->foreign('matric_number')->references('matric_number')->on('StudentDetails');
        });

        Schema::create('InternationalStudent', function (Blueprint $table) {
            $table->unsignedInteger('customerID')->primary();
            $table->string('matric_number', 30)->nullable();
            $table->foreign('customerID')->references('customerID')->on('International');
            $table->foreign('matric_number')->references('matric_number')->on('StudentDetails');
        });

        Schema::create('Local_UTMStaff', function (Blueprint $table) {
            $table->unsignedInteger('customerID')->primary();
            $table->string('staff_number', 30)->nullable();
            $table->foreign('customerID')->references('customerID')->on('Local');
            $table->foreign('staff_number')->references('staff_number')->on('StaffDetails');
        });

        Schema::create('International_UTMStaff', function (Blueprint $table) {
            $table->unsignedInteger('customerID')->primary();
            $table->string('staff_number', 30)->nullable();
            $table->foreign('customerID')->references('customerID')->on('International');
            $table->foreign('staff_number')->references('staff_number')->on('StaffDetails');
        });

        // ===== STAFF SPECIALIZATION =====
        Schema::create('Runner', function (Blueprint $table) {
            $table->unsignedInteger('staffID')->primary();
            $table->decimal('commission', 10, 2)->nullable();
            $table->foreign('staffID')->references('staffID')->on('Staff');
        });

        Schema::create('StaffIT', function (Blueprint $table) {
            $table->unsignedInteger('staffID')->primary();
            $table->decimal('salary', 10, 2)->nullable();
            $table->foreign('staffID')->references('staffID')->on('Staff');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop in reverse order of creation (respecting foreign keys)
        Schema::dropIfExists('StaffIT');
        Schema::dropIfExists('Runner');
        Schema::dropIfExists('International_UTMStaff');
        Schema::dropIfExists('Local_UTMStaff');
        Schema::dropIfExists('InternationalStudent');
        Schema::dropIfExists('LocalStudent');
        Schema::dropIfExists('StaffDetails');
        Schema::dropIfExists('StudentDetails');
        Schema::dropIfExists('International');
        Schema::dropIfExists('Local');
        Schema::dropIfExists('BrowseHistory');
        Schema::dropIfExists('SystemLog');
        Schema::dropIfExists('LoyaltyCard');
        Schema::dropIfExists('WalletAccount');
        Schema::dropIfExists('Car_Img');
        Schema::dropIfExists('GrantDoc');
        Schema::dropIfExists('Roadtax');
        Schema::dropIfExists('Insurance');
        Schema::dropIfExists('VehicleDocument');
        Schema::dropIfExists('VehicleMaintenance');
        Schema::dropIfExists('Review');
        Schema::dropIfExists('AdditionalCharges');
        Schema::dropIfExists('Invoice');
        Schema::dropIfExists('Payment');
        Schema::dropIfExists('Booking');
        Schema::dropIfExists('Motorcycle');
        Schema::dropIfExists('Car');
        Schema::dropIfExists('Vehicle');
        Schema::dropIfExists('OwnerCar');
        Schema::dropIfExists('Admin');
        Schema::dropIfExists('Staff');
        Schema::dropIfExists('Customer');
        Schema::dropIfExists('User');
        Schema::dropIfExists('PersonDetails');
    }
};
