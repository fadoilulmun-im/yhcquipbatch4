@extends('master')
@section('content')
  <div class="container">
    <div class="card">
      <div class="card-header d-flex justify-content-between">
        <h5>Students</h5>
        <button class="btn btn-primary note-btn" id="add" onclick="add()" >Add New</button>
      </div>
      <div class="card-body table-responsive">
        <table class="table table-sm text-left" id="student-table">
          <thead>
            <tr>
              <th scope="col">No</th>
              <th scope="col">Name</th>
              <th scope="col">Major</th>
              <th scope="col">Semester</th>
              <th scope="col">Class</th>
              <th scope="col">Batch Year</th>
              <th scope="col">Action</th>
            </tr>
          </thead>
        </table>
      </div>
    </div>
  </div>
  
@endsection

@section('js-page')
  <script>
    let datatable = $('#student-table').DataTable({
      processing: true,
      serverSide: true,
      ajax: "/",
      columns: [
        { data: 'DT_RowIndex', name: 'id', orderable: true, searchable: false },
        { data: 'name'},
        { data: 'major_name', name: 'majors.name', defaultContent: "-"},
        { data: 'semester', name: 'batch_year', defaultContent: "-"},
        { data: 'class', name: 'class' },
        { data: 'batch_year', name: 'batch_year' },
        { data: 'action', name: 'action', orderable: false, searchable: false}
      ],
      order: [[0, 'desc']],
    });
    
    function destroy(id){
      Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!',
        showLoaderOnConfirm: true,
        preConfirm: () => {
          return fetch(`/api/student/${id}`, {
            method: 'DELETE',
          })
          .then(response => {
            if (!response.ok) {
              throw new Error(response.statusText)
            }
            return response.json()
          })
          .catch(error => {
            Swal.showValidationMessage(
              `Request failed: ${error}`
            )
          })
        },
        allowOutsideClick: () => !Swal.isLoading(),
      }).then((result) => {
        if (result.isConfirmed) {
          Swal.fire(
            'Deleted!',
            'Data has been deleted.',
            'success'
          );
          datatable.ajax.reload();
        }
      })
    }

    function show(id){
      Swal.fire({
        title: '<h4>Detail Student</h4>',
        html:`<div class="spinner-border" role="status">
            <span class="sr-only">Loading...</span>
          </div>`,
        showCloseButton: true,
        showConfirmButton: false,
        willOpen: () => {
          $.ajax({
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            dataType: 'json',
            url: `/api/student/${id}`,
            success: function(response){
              $('#swal2-html-container').html(`
              <table class="table text-left">
                <tr>
                  <th>Name</th>
                  <td>${response.name ?? '-'}</td>
                </tr>
                <tr>
                  <th>Major</th>
                  <td>${response?.major?.name ?? '-'}</td>
                </tr>
                <tr>
                  <th>Semester</th>
                  <td>${response.semester ?? '-'}</td>
                </tr>
                <tr>
                  <th>Class</th>
                  <td>${response.class ?? '-'}</td>
                </tr>
                <tr>
                  <th>Batch Year</th>
                  <td>${response.batch_year ?? '-'}</td>
                </tr>
              </table>
            `)
            },
          }).always(function(res) {
            // console.log(res);
          });
        }
      })
    }

    function edit(id){
      Swal.fire({
        title: '<h4>Update Student</h4>',
        html:`<div class="spinner-border" role="status">
            <span class="sr-only"></span>
          </div>`,
        showCloseButton: true,
        showCancelButton: true,
        focusConfirm: true,
        showConfirmButton: true,
        confirmButtonText: 'Save',
        willOpen: () => {
          $.ajax({
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            dataType: 'json',
            url: `/api/student/${id}`,
            success: function(response){
              $('#swal2-html-container').html(`
                <form>
                  <table class="table text-left">
                    <tr>
                      <th><label for="edit-name">Name</label></th>
                      <td><input type="text" class="form-control" id="edit-name" name="name" value="${response.name ?? ''}" required></td>
                    </tr>
                    <tr>
                      <th><label for="edit-major">Major</label></th>
                      <td>
                        <select class="form-control" id="edit-major" name="major_id" required>
                          ${({!! json_encode($Majors) !!}).map(function(d){
                            return `<option value="${d.id}" ${response?.major?.id == d.id ? "selected" : ""}>${d.name}</option>`
                          })}
                        </select>
                      </td>
                    </tr>
                    <tr>
                      <th><label for="edit-semester">Semester</label></th>
                      <td><input type="text" class="form-control" readonly id="edit-semester" name="name" value="${response.semester ?? ''}"></td>
                    </tr>
                    <tr>
                      <th><label for="edit-class">Class</label></th>
                      <td>
                        <select class="form-control" id="edit-class" name="class" required>
                          <option value="Regular" ${response.class == "Regular" ? "selected" : ""}>Regular</option>
                          <option value="International" ${response.class == "International" ? "selected" : ""}>International</option>
                        </select>  
                      </td>
                    </tr>
                    <tr>
                      <th><label for="edit-year">Batch Year</label></th>
                      <td><input type="number" class="form-control" id="edit-year" name="batch_year" value="${response.batch_year ?? ''}" required></td>
                    </tr>
                  </table>
                </form>
            `)
            },
          }).always(function(res) {
            // console.log(res);
          });
        },
        showLoaderOnConfirm: true,
        preConfirm: () => {
          let data = {
            name: $('#edit-name').val(),
            major_id: $('#edit-major').val(),
            class: $('#edit-class').val(),
            batch_year: $('#edit-year').val(),
          };

          return $.ajax({
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            type: "PUT",
            dataType: 'json',
            url: `/api/student/${id}`,
            data: data,
            success: function(response){
              Swal.fire(
                'Success!',
                'Data has been updated.',
                'success'
              );
              datatable.ajax.reload();
            }
          }).always(function(res) {
            // console.log(res);
          });
        },
        allowOutsideClick: () => !Swal.isLoading(),
      })
    }

    function add() {
      Swal.fire({
        title: '<h4>Add New Student</h4>',
        html:`
          <form>
            <table class="table text-left">
              <tr>
                <th><label for="add-name">Name</label></th>
                <td><input type="text" class="form-control" id="add-name" name="name" required></td>
              </tr>
              <tr>
                <th><label for="add-major">Major</label></th>
                <td>
                  <select class="form-control" id="add-major" name="major_id" required>
                    ${({!! json_encode($Majors) !!}).map(function(d){
                      return `<option value="${d.id}">${d.name}</option>`
                    })}
                  </select>
                </td>
              </tr>
              <tr>
                <th><label for="add-semester">Semester</label></th>
                <td><input type="text" class="form-control" readonly id="add-semester" name="name"></td>
              </tr>
              <tr>
                <th><label for="add-class">Class</label></th>
                <td>
                  <select class="form-control" id="add-class" name="class" required>
                    <option value="Regular">Regular</option>
                    <option value="International">International</option>
                  </select>  
                </td>
              </tr>
              <tr>
                <th><label for="add-year">Batch Year</label></th>
                <td><input type="number" class="form-control" id="add-year" name="batch_year" required></td>
              </tr>
            </table>
          </form>
        `,
        showCloseButton: true,
        showCancelButton: true,
        focusConfirm: true,
        showConfirmButton: true,
        confirmButtonText: 'Save',
        showLoaderOnConfirm: true,
        preConfirm: () => {
          let data = {
            name: $('#add-name').val(),
            major_id: $('#add-major').val(),
            class: $('#add-class').val(),
            batch_year: $('#add-year').val(),
          };

          return $.ajax({
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            type: "POST",
            dataType: 'json',
            url: `/api/student`,
            data: data,
            success: function(response){
              Swal.fire(
                'Success!',
                'Data has been added.',
                'success'
              );
              datatable.ajax.reload();
            }
          }).always(function(res) {
            // console.log(res);
          });
        },
        allowOutsideClick: () => !Swal.isLoading(),
      })
    }
  </script>
@endsection