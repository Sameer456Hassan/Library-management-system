// Function to handle signup
function signup() {
  var username = $("#signup-username").val();
  var email = $("#signup-email").val();
  var password = $("#signup-password").val();
  var role = $("#signup-role").val();

  $.ajax({
    type: "POST",
    url: "/signup", // Replace with the actual URL of your signup API endpoint
    data: {
      username: username,
      email: email,
      password: password,
      role: role,
    },
    success: function (response) {
      console.log(response);
      document.cookie = `token=${response.token}; path=/`;
      // Show success message using SweetAlert2
      Swal.fire({
        icon: "success",
        title: "Signup Successful",
        text: "Account created successfully!",
      }).then(function () {
        window.location.href = "/dashboard";
      });
    },
    error: function (error) {
      // Handle signup error, e.g., display an error message
      console.log(error);
      // Show error message using SweetAlert2
      Swal.fire({
        icon: "error",
        title: "Signup Error",
        text: error.responseJSON.error,
      });
    },
  });
}

// Function to handle login
function login() {
  var email = $("#login-email").val();
  var password = $("#login-password").val();

  $.ajax({
    type: "POST",
    url: "/login", // Replace with the actual URL of your login API endpoint
    data: {
      email: email,
      password: password,
    },
    success: function (response) {
      // Handle successful login, e.g., store the token in localStorage
      console.log(response);
      document.cookie = `token=${response.token}; path=/`;

      // Show success message using SweetAlert2
      Swal.fire({
        icon: "success",
        title: "Login Successful",
        text: "You are now logged in!",
      }).then(function () {
        window.location.href = "/dashboard";
      });
    },
    error: function (error) {
      // Handle login error, e.g., display an error message
      console.log(error);
      // Show error message using SweetAlert2
      Swal.fire({
        icon: "error",
        title: "Login Error",
        text: error.responseText,
      });
    },
  });
}
