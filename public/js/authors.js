document.addEventListener('DOMContentLoaded', function () {
  fetchAuthors('');
});

function applyFilters() {
  fetchAuthors(getSearchParams());
}
function getSearchParams() {
  const page = parseInt($('#pageInput').val()) || 1;
  const sort_by = $('#sortSelect').val();
  const sort_order = $('#sortOrderSelect').val();
  const limit = parseInt($('#limitSelect').val());
  const search = $('#searchInput').val();

  // Create an object with only non-empty values
  const params = {
    page: page || undefined,
    sort_by: sort_by || undefined,
    sort_order: sort_order || undefined,
    limit: limit || undefined,
    search: search || undefined,
  };

  // Filter out undefined values
  const filteredParams = Object.fromEntries(
    Object.entries(params).filter(([_, v]) => v !== undefined)
  );

  return filteredParams;
}

function fetchAuthors(params) {
  // Create a query string from the parameters
  const queryString = new URLSearchParams(params).toString();

  // Fetch authors from the server using Axios
  axios
    .get(`http://localhost:8000/authors?${queryString}`, {
      headers: {
        'X-Api-Token': 'your-secret-token',
      },
    })
    .then((response) => {
      const data = response.data;

      // Render the table with author data
      renderTable(data);
    })
    .catch((error) => {
      console.log(error);
    });
}

function renderTable(data) {
  // Clear existing table rows
  $('#authorsTable tbody').empty();

  // Populate table with fetched data
  data.forEach((author) => {
    $('#authorsTable tbody').append(`
        <tr>
          <td>${author.id}</td>
          <td>${author.name}</td>
          <td>${author.dob}</td>
          <td><button class="btn btn-danger" data-id="${author.id}" onclick="deleteAuthor(${author.id})"><i class="fa fa-trash"></i> Delete</button>
          <button data-author-id="${author.id}" data-author-name="${author.name}" data-author-dob="${author.dob}" class="btn btn-secondary edit-author-btn"><i class="fa fa-pen"></i> Edit</button></td>
        </tr>
      `);
  });
}

function deleteAuthor(authorId) {
  // Get the authentication token from the cookie
  const authToken = getAuthTokenFromCookie();

  // Make a request to fetch author data using Axios
  axios
    .delete(`http://localhost:8000/authors/${authorId}`, {
      headers: {
        Authorization: `Bearer ${authToken}`,
      },
    })
    .then((response) => {
      console.log(response);
      Swal.fire({
        title: response.data,
        icon: 'warning',
      });
    })
    .catch((error) => {
      Swal.fire({
        title: 'Only Librarians Allowed',
        icon: 'error',
        text: error.message,
      });
    });
}

function getAuthTokenFromCookie() {
  const authToken = Cookies.get('token');

  return authToken || '';
}

$('#authorsTable').on('click', '.edit-author-btn', function () {
  var authorId = $(this).data('author-id');
  var authorName = $(this).data('author-name');
  var authorDOB = $(this).data('author-dob');

  $('#editAuthorId').val(authorId);
  $('#editAuthorName').val(authorName);
  $('#editAuthorDOB').val(authorDOB);

  $('#editAuthorModal').modal('show');
});

// Event listener for submitting the edit author form
$('#editAuthorForm').submit(function (event) {
  event.preventDefault();

  const authorId = $('#editAuthorId').val();
  const updatedAuthor = {
    name: $('#editAuthorName').val(),
    dob: $('#editAuthorDOB').val(),
  };

  updateAuthor(authorId, updatedAuthor);

  // Close the edit modal after submitting
  $('#editAuthorModal').modal('hide');
});

function updateAuthor(authorId, updatedAuthor) {
  // Get the authentication token from the cookie
  const authToken = getAuthTokenFromCookie();
  // Make a request to update author data using Axios
  axios
    .put(`http://localhost:8000/authors/${authorId}`, updatedAuthor, {
      headers: {
        Authorization: `Bearer ${authToken}`,
      },
    })
    .then((response) => {
      console.log(response);
      Swal.fire({
        title: response.data.message,
        icon: 'success',
      });

      // Fetch authors again after successful update
      fetchAuthors(getSearchParams());
    })
    .catch((error) => {
      Swal.fire({
        title: 'Only Librarians Allowed',
        icon: 'error',
        text: error.message,
      });
    });
}

// Event listener for submitting the add author form
$('#addAuthorForm').submit(function (event) {
  event.preventDefault();

  const newAuthor = {
    name: $('#authorName').val(),
    dob: $('#authorDOB').val(),
  };

  // Call the function to add a new author
  addAuthor(newAuthor);

  // Close the add modal after submitting
  $('#addAuthorModal').modal('hide');
});

// Function to add a new author
function addAuthor(newAuthor) {
  // Get the authentication token from the cookie
  const authToken = getAuthTokenFromCookie();

  // Make a request to add a new author using Axios
  axios
    .post('http://localhost:8000/authors', newAuthor, {
      headers: {
        Authorization: `Bearer ${authToken}`,
      },
    })
    .then((response) => {
      console.log(response);
      Swal.fire({
        title: response.data,
        icon: 'success',
      });

      // Fetch authors again after successful addition
      fetchAuthors(getSearchParams());
    })
    .catch((error) => {
      Swal.fire({
        title: 'Only Librarians Allowed',
        icon: 'error',
        text: error.message,
      });
    });
}
