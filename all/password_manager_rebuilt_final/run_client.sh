#!/bin/bash
# run_client.sh - Start the Password Manager Client GUI (Rebuild)

# --- Configuration ---
# Explicitly set the path to the Java 17 JDK runtime
JAVA17_HOME="/usr/lib/jvm/java-17-openjdk-amd64"
JAVA="${JAVA17_HOME}/bin/java"

# Classpath: Needs to point to the directory containing the compiled classes
BIN_DIR="bin"
CLASSPATH="${BIN_DIR}"

# --- Determine Absolute Path for Codebase ---
# Get the absolute path to the directory containing this script
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" &> /dev/null && pwd)"
# Construct the absolute path to the bin directory
ABS_BIN_DIR="${SCRIPT_DIR}/${BIN_DIR}"
# Format as a file URL for the codebase property (ensure trailing slash)
CODEBASE_URL="file:${ABS_BIN_DIR}/"

# --- Check Prerequisites ---
if [ ! -x "${JAVA}" ]; then
    echo "Error: Java 17 runtime not found or not executable at ${JAVA}"
    exit 1
fi
if [ ! -d "${BIN_DIR}" ]; then
    echo "Error: Class files directory '${BIN_DIR}' not found. Run compile.sh first."
    exit 1
fi

# --- Start Password Manager Client ---
echo "Starting Password Manager Client..."
echo "Using codebase: ${CODEBASE_URL}"

# Run the Client class using the explicit Java path, classpath, and codebase property
# Note: Codebase might not be strictly necessary for the client if it doesn't download
# stubs dynamically, but setting it ensures consistency and helps in some scenarios.
"${JAVA}" \
    -cp "${CLASSPATH}" \
    -Djava.rmi.server.codebase="${CODEBASE_URL}" \
    com.example.passwordmanager.Client

# Check if the client started successfully (the Java command itself might error out)
if [ $? -ne 0 ]; then
    echo "Error: Failed to start Password Manager Client. Check Java output above for details."
    exit 1
fi

# Note: If the client starts successfully, it will run in the foreground
# until the GUI window is closed.
exit 0

