document.addEventListener('DOMContentLoaded', function () {
  fetchBooks('');
});

function applyFilters() {
  fetchBooks(getSearchParams());
}

function getSearchParams() {
  const page = parseInt($('#pageInput').val()) || 1;
  const sort_by = $('#sortSelect').val();
  const sort_order = $('#sortOrderSelect').val();
  const limit = parseInt($('#limitSelect').val()) ;
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
  const filteredParams = Object.fromEntries(Object.entries(params).filter(([_, v]) => v !== undefined));

  return filteredParams;
}

function fetchBooks(params) {
  // Create a query string from the parameters
  const queryString = Object.keys(params)
    .map((key) => `${encodeURIComponent(key)}=${encodeURIComponent(params[key])}`)
    .join('&');

  // Fetch books from the server
  fetch(`http://localhost:8000/books?${queryString}`, {
    method: 'GET',
    headers: {
      'X-Api-Token': 'your-secret-token',
    },
  })
    .then((response) => response.json())
    .then((data) => {
      // Iterate through each book and fetch author name
      Promise.all(
        data.map((book) => {
          // Make a new request to get author name
          return fetch(`http://localhost:8000/authors/${book.author_id}`, {
            method: 'GET',
            headers: {
              Authorization: 'Basic ' + btoa('librarian1@example.com:password'),
            },
          })
            .then((response) => response.json())
            .then((authorData) => {
              // Add author name to the book object
              book.author_name = authorData.name;
            });
        })
      )
        .then(() => {
          // After fetching all author names, render the table
          renderTable(data);
        })
        .catch((error) => {
          console.log(error);
        });
    })
    .catch((error) => {
      console.log(error);
    });
}

function renderTable(data) {
  // Clear existing table rows
  $('#booksTable tbody').empty();

  // Populate table with fetched data
  data.forEach((book) => {
    $('#booksTable tbody').append(`
      <tr>
        <td>${book.name}</td>
        <td>${book.author_name}</td>
        <td>${book.version}</td>
        <td>${book.isbn_code}</td>
        <td>${book.release_date}</td>
        <td>${book.shelf_position}</td>
      </tr>
    `);
  });
}
