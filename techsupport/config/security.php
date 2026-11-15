<?php
// SANITIZING INPUT
// Cleans text typed by the user before it is saved:
//   trim()              removes extra spaces at the start and end
//   stripslashes()      removes backslashes
//   htmlspecialchars()  turns < > " ' into harmless text so scripts cannot run
function sanitize_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
  return $data;
}

// VALIDATING PASSWORD SECURITY
// Checks that the password has at least 8 characters, an uppercase letter,
// a lowercase letter, and a number. Returns '' if it is OK,
// otherwise returns the message to show. (Never sanitize a password.)
function password_error($pw) {
  if (strlen($pw) < 8) {
    return 'Password must be at least 8 characters long.';
  }
  if (!preg_match('/[A-Z]/', $pw)) {
    return 'Password must contain at least one UPPERCASE letter.';
  }
  if (!preg_match('/[a-z]/', $pw)) {
    return 'Password must contain at least one lowercase letter.';
  }
  if (!preg_match('/[0-9]/', $pw)) {
    return 'Password must contain at least one number.';
  }
  return '';
}