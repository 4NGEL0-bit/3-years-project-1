#!/bin/bash
# compile.sh - Compile Java source files for the Password Manager (Rebuild)

# --- Configuration ---
# Explicitly set the path to the Java 17 JDK compiler
# Ensure this path matches your Java 17 installation
JAVA17_HOME="/usr/lib/jvm/java-17-openjdk-amd64"
JAVAC="${JAVA17_HOME}/bin/javac"

# Source directory containing .java files
SRC_DIR="src/main/java"
# Output directory for compiled .class files
BIN_DIR="bin"

# --- Check for Java Compiler ---
if [ ! -x "${JAVAC}" ]; then
    echo "Error: Java 17 compiler (javac) not found or not executable at ${JAVAC}"
    echo "Please ensure OpenJDK 17 is installed correctly and the JAVA17_HOME path is correct in this script."
    exit 1
fi

# --- Compilation ---
echo "Removing old class files from ${BIN_DIR} (if any)..."
rm -rf "${BIN_DIR}"

echo "Creating output directory: ${BIN_DIR}"
mkdir -p "${BIN_DIR}"

echo "Compiling Java source files from ${SRC_DIR} using ${JAVAC}..."

# Find all .java files and compile them, placing .class files into BIN_DIR
# Using -d specifies the output directory for class files
# Using -cp specifies the classpath (needed if there were external libraries)
"${JAVAC}" -d "${BIN_DIR}" -cp "${SRC_DIR}" $(find "${SRC_DIR}" -name '*.java')

# Check if compilation was successful
if [ $? -ne 0 ]; then
    echo "Error: Compilation failed."
    exit 1
else
    echo "Compilation successful. Class files are in ${BIN_DIR}"
    exit 0
fi

