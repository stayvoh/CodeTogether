# CodeTogether

A collaborative website for CS students!

## Prerequisites

Make sure Docker is installed on your system before proceeding.

## Setup

### 1. Clone the Repository

After cloning the repository, you'll need to create an environment file.

### 2. Environment Configuration

Create a `.env` file in the `CodeTogether/CodeTogether` directory. 

**Note:** Reach out to one of the original developers for the correct `.env` file contents.

### 3. Create Uploads Directory (Non-Linux Systems)

If you're **not** on Linux, ensure that the following directory exists:

```
CodeTogether/CodeTogether/app/public/uploads
```

## Running the Application

### On Linux

If you're on Linux, you can use the provided shell scripts (requires sudo privileges):

```bash
./docker-compose-start.sh -d --build
```

### On Other Systems

If you're not on Linux, execute Docker commands manually:

```bash
docker compose up -d --build
```

## Utility Scripts (Linux)

### Fresh Database

Restart the containers with a fresh database:

```bash
./freshDb.sh
```

### Database Access

Execute into the database container:

```bash
./dbMod.sh
```

## Architecture

The webapp follows an **MVC architecture**:

```
Request → Router → Controller → View
```