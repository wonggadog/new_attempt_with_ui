// DOM Elements
const userForm = document.getElementById("userForm")
const userTableBody = document.getElementById("userTableBody")
const messageElement = document.getElementById("message")

// Load existing users from localStorage
document.addEventListener("DOMContentLoaded", loadUsers)

// Form submission event
userForm.addEventListener("submit", (event) => {
  event.preventDefault()

  // Get form values
  const name = document.getElementById("name").value.trim()
  const email = document.getElementById("email").value.trim()
  const idNumber = document.getElementById("idNumber").value.trim()
  const department = document.getElementById("department").value
  const password = document.getElementById("password").value

  // Validate form
  if (!validateForm(name, email, idNumber, department, password)) {
    return
  }

  // Create user object
  const user = {
    id: generateId(),
    name,
    email,
    idNumber,
    department,
    password, // In a real application, this should be hashed
  }

  // Save user to "database" (localStorage)
  saveUser(user)

  // Add user to table
  addUserToTable(user)

  // Show success message
  showMessage("User added successfully!", "success")

  // Reset form
  userForm.reset()
})

// Validate form inputs
function validateForm(name, email, idNumber, department, password) {
  // Reset message
  messageElement.className = "message"
  messageElement.style.display = "none"

  // Basic validation
  if (!name || !email || !idNumber || !department || !password) {
    showMessage("All fields are required", "error")
    return false
  }

  // Email validation
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  if (!emailRegex.test(email)) {
    showMessage("Please enter a valid email address", "error")
    return false
  }

  // ID Number validation (assuming it should be numeric)
  //if (isNaN(idNumber)) {
    //showMessage("ID Number should be numeric", "error")
    //return false
  //}

  // Password validation (minimum 6 characters)
  if (password.length < 6) {
    showMessage("Password must be at least 6 characters long", "error")
    return false
  }

  return true
}

// Generate a unique ID
function generateId() {
  return Date.now().toString(36) + Math.random().toString(36).substr(2)
}

// Save user to localStorage
function saveUser(user) {
  const users = getUsers()
  users.push(user)
  localStorage.setItem("users", JSON.stringify(users))
}

// Get users from localStorage
function getUsers() {
  const users = localStorage.getItem("users")
  return users ? JSON.parse(users) : []
}

// Load users from localStorage and display in table
function loadUsers() {
  const users = getUsers()
  users.forEach((user) => addUserToTable(user))
}

// Add user to the table
function addUserToTable(user) {
  const row = document.createElement("tr")

  row.innerHTML = `
        <td>${user.name}</td>
        <td>${user.email}</td>
        <td>${user.idNumber}</td>
        <td>${user.department}</td>
        <td>
            <button class="btn-delete" data-id="${user.id}">Delete</button>
        </td>
    `

  // Add delete event listener
  row.querySelector(".btn-delete").addEventListener("click", () => {
    deleteUser(user.id, row)
  })

  userTableBody.appendChild(row)
}

// Delete user
function deleteUser(id, row) {
  // Remove from localStorage
  let users = getUsers()
  users = users.filter((user) => user.id !== id)
  localStorage.setItem("users", JSON.stringify(users))

  // Remove from table
  row.remove()

  // Show message
  showMessage("User deleted successfully!", "success")
}

// Show message
function showMessage(message, type) {
  messageElement.textContent = message
  messageElement.className = `message ${type}`
  messageElement.style.display = "block"

  // Hide message after 3 seconds
  setTimeout(() => {
    messageElement.style.display = "none"
  }, 3000)
}

