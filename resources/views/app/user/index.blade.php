@extends('layouts.app')

@push('css')
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs4/dt-1.11.3/r-2.2.9/datatables.min.css"/>
@endpush

@section('content')
    <div class="card">
        <div class="card-header">
            <h2>{{ __('List User') }}</h2>
        </div>

        <div class="card-body">
            <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#formModal">Add User</button>
            <table class="table table-striped responsive" id="data-table" width="100%">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
    <!-- Modal -->
    <div class="modal fade" id="formModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="formModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('user.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="formModalLabel">Add User</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <img id="profilePicture" src="{{ asset('storage/user/default.png') }}" class="img-thumbnail" alt="">
                        <div class="mb-3">
                            <label for="formFile" class="form-label">Profile Picture</label>
                            <input class="form-control" accept="image/*" type="file" id="formFile" name="profile_picture" required>
                        </div>
                        <div class="mb-3">
                            <label>Name</label>
                            <input class="form-control" type="text" name="name" placeholder="input name" required>
                        </div>
                        <div class="mb-3">
                            <label>Email</label>
                            <input class="form-control" type="email" name="email" placeholder="input email" required>
                        </div>
                        <div class="mb-3">
                            <label>Password</label>
                            <input class="form-control" type="text" name="password" placeholder="input password" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script type="text/javascript" src="https://cdn.datatables.net/v/bs4/dt-1.11.3/r-2.2.9/datatables.min.js"></script>
    <script>
         const dataTable = $('#data-table').DataTable({
            searching: true,
            responsive: true,
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{route('user')}}",
                error: function (xhr, error, code){
                    if(code=="Unauthorized"){
                        location.reload();
                    }
                }
            },
            columns: [
                {
                    data: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'email',
                    name: 'email'
                },
                {
                    searchable: false,
                    sortable : false,
                    data: 'action',
                    name: 'action'
                }
            ],
        });

        window.deleteData = function (id, name) {
            Swal.fire({
                allowEnterKey:true,
                title: 'Are you sure ?',
                html: "Data <b>"+name+" </b> will be delete permanently",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes',
                cancelButtonText: 'No',
                showLoaderOnConfirm: true,
                preConfirm: async () => {
                    try {
                        const response = await window.axios.delete('user/destroy/'+id);
                        if (response.statusText!="OK") {
                            throw new Error(response.statusText);
                        }
                        return await response;
                    } catch (error) {
                        Swal.showValidationMessage(
                            `Error: ${error}`
                        );
                    }
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Success!',
                        html: 'Data <b>'+name+'</b> has been deleted!',
                        icon: 'success'
                    });
                    dataTable.ajax.reload(null, false);
                }
            })
        }

        window.editFormModal = function (id) {
            $('form').attr('action','user/update/'+id);
            $('#formModalLabel').html('Edit User');
            $('button[type="submit"]').text('Update');
            $('#formModal').modal('show');
            $('button[type="submit"]').attr('disabled',true);
            $('.loading-modal').show();
            $('.content-modal').hide();
            window.axios.get('/user/show/'+id).then(function (response) {
                $('button[type="submit"]').attr('disabled',false);
                const data = response.data;
                $('input[name="name"]').val(data.name);
                $('input[name="email"]').val(data.email);
                $('#profilePicture').attr('src', "{{ asset('storage') }}/user/"+data.profile_picture)
            }).catch(function (error) {
                console.log(error.message);
            });
        }


        $('#formModal').on('hidden.bs.modal', function (event) {
            $('#profilePicture').attr('src', "{{ asset('storage') }}/user/default.png")
            $('#formModalLabel').html('Add User');
            $('form').attr('action','store');
            $('button[type="submit"]').attr('disabled',false);
            $('button[type="submit"]').text('Save');
        });
        let imgInp = document.getElementById("formFile");
        let blah = document.getElementById("profilePicture");
        imgInp.onchange = evt => {
            const [file] = imgInp.files
            if (file) {
                blah.src = URL.createObjectURL(file)
            }
        }


    </script>
@endpush
