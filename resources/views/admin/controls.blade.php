@extends('layouts.app')

@section('content')
<div class="container">
    <header>
        <h1>Admin Dashboard</h1>
    </header>
    
    <main>
        <section class="form-section">
            <h2>Add New User</h2>
            <form id="userForm">
                @csrf
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="idNumber">ID Number</label>
                    <input type="text" id="idNumber" name="idNumber" required>
                </div>
                
                <div class="form-group">
                    <label for="department">Department</label>
                    <select id="department" name="department" required>
                        <option value="">Select Department</option>
                        <option value="Admin">Admin</option>
                        <option value="Budget">Budget</option>
                        <option value="Accounting">Accounting</option>
                        <option value="Supply">Supply</option>
                        <option value="BACS">BACS</option>
                        <option value="Cashier">Cashier</option>
                        <option value="Registrar">Registrar</option>
                        <option value="Biology">Biology</option>
                        <option value="Chemistry">Chemistry</option>
                        <option value="Computer Science">Computer Science</option>
                        <option value="Information Technology">Information Technology</option>
                        <option value="Physics">Physics</option>
                        <option value="Meteorology">Meteorology</option>
                        <option value="Mathematics">Mathematics</option>
                        <option value="Computer Laboratory">Computer Laboratory</option>
                        <option value="NatSci Lab">NatSci Lab</option>
                        <option value="Others">Others</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn-submit">Add User</button>
                    <button type="reset" class="btn-reset">Reset</button>
                </div>
            </form>
            <div id="message" class="message"></div>
        </section>
        
        <section class="data-section">
            <h2>User Database</h2>
            <div class="table-container">
                <table id="userTable">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>ID Number</th>
                            <th>Department</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="userTableBody">
                        @foreach($users as $user)
                        <tr data-id="{{ $user->id }}">
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->id_number }}</td>
                            <td>{{ $user->department }}</td>
                            <td>
                                <button class="btn-delete" data-id="{{ $user->id }}">Delete</button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/admin_controls.js') }}"></script>
@endpush