# TaskTrackerApi

TaskTrackerApi is an advanced RESTful API built using the CodeIgniter 3 framework. It provides a comprehensive task management system with user authentication and authorization using token-based bearer token authentication.

![Screenshot-20230513172045-1471x872.png](showcase%2FScreenshot-20230513172045-1471x872.png)

## Features

- User Management:
    - Sign up: Allows users to register for an account.
    - Sign in: Authenticates users and provides them with a bearer token upon successful login.
    - Sign out: Invalidates the token, logging out the user and preventing further access.
    - Fetch user info: Retrieves user details based on the provided token.
    - Update user info: Allows users to modify their account information.

- Task Management:
    - Add task: Enables users to create new tasks associated with their account.
    - Update task: Allows users to modify existing tasks.
    - Delete task: Deletes a specified task.
    - View tasks: Retrieves a list of tasks associated with the user's account.

## Installation

1. Clone the repository:

```bash
git clone https://github.com/your-username/TaskTrackerApi.git
```

2. Configure the database settings in application/config/database.php.
3. Import the db into your mysql databasae from /db directory


## API Endpoints

## Sign up
POST /authentication/processSignUp

Content-Type: multipart/form-data
```
"username": "johndoe",
"name": "Jhon Doe",
"password": "password123"
```

## Sign in
POST /authentication 

Content-Type: multipart/form-data
```
"username": "johndoe",
"password": "password123"
```

After Signing in you will receive a token in the response body

1. In your Further Request add the following header.
2. Replace <your_token> with the signin token
```
Authorization: Bearer <your_token>
```

## Sign Out
DELETE /authentication

## Get Current User Info
GET /user

## Update Current User Info
PUT /user

Content-Type: text/plain
```
"username": "johndoe",
"name": "Jhon Doe",
"old_password": "password123",
"new_password": "password456"
```

# Get task lists
GET /tasks

## Create a new task
POST /task

Content-Type: text/plain
```
"title": "Buy groceries",
"Description": "Buy milk, eggs, and bread",
"Status": "pending"
```

## Get information about a specific task
GET /task/1

## Update a specific task
PUT /task/

Content-Type: text/plain
```
"id": "1",
"task_name": "Buy groceries",
"task_description": "Buy milk, eggs, and bread",
"task_status": "completed"
```

# Delete a specific task
DELETE /tasks

Content-Type: text/plain
```
"id": "1",
```

<!-- LICENSE -->
## License

Distributed under the MIT License. See `LICENSE.txt` for more information.
