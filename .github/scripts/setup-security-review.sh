#!/bin/bash

# Setup script for automated security reviews
# This script helps configure the Claude API key for GitHub Actions

set -e

echo "========================================"
echo "  Automated Security Review Setup"
echo "========================================"
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if we're in a git repository
if ! git rev-parse --git-dir > /dev/null 2>&1; then
    echo -e "${RED}Error: Not in a git repository${NC}"
    exit 1
fi

# Check if GitHub CLI is installed
if ! command -v gh &> /dev/null; then
    echo -e "${YELLOW}GitHub CLI (gh) is not installed.${NC}"
    echo "Please install it from: https://cli.github.com/"
    echo ""
    echo "Or add the secret manually in your GitHub repository settings:"
    echo "Settings → Secrets and variables → Actions → New repository secret"
    echo "Name: ANTHROPIC_API_KEY"
    exit 1
fi

# Check if user is authenticated with GitHub CLI
if ! gh auth status &> /dev/null; then
    echo -e "${YELLOW}You need to authenticate with GitHub CLI first.${NC}"
    echo "Run: gh auth login"
    exit 1
fi

# Get repository information
REPO=$(gh repo view --json nameWithOwner -q .nameWithOwner)
if [ -z "$REPO" ]; then
    echo -e "${RED}Error: Could not determine repository name${NC}"
    exit 1
fi

echo "Repository: $REPO"
echo ""

# Check if ANTHROPIC_API_KEY secret already exists
echo "Checking for existing ANTHROPIC_API_KEY secret..."
if gh secret list | grep -q "ANTHROPIC_API_KEY"; then
    echo -e "${YELLOW}ANTHROPIC_API_KEY secret already exists.${NC}"
    read -p "Do you want to update it? (y/n): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        echo "Keeping existing secret."
    else
        echo ""
        echo "Enter your Anthropic API key (it will be hidden):"
        read -s API_KEY
        echo ""
        
        if [ -z "$API_KEY" ]; then
            echo -e "${RED}Error: API key cannot be empty${NC}"
            exit 1
        fi
        
        echo "$API_KEY" | gh secret set ANTHROPIC_API_KEY
        echo -e "${GREEN}✓ ANTHROPIC_API_KEY secret updated successfully${NC}"
    fi
else
    echo ""
    echo "Enter your Anthropic API key (it will be hidden):"
    echo "Get your API key from: https://console.anthropic.com/settings/keys"
    read -s API_KEY
    echo ""
    
    if [ -z "$API_KEY" ]; then
        echo -e "${RED}Error: API key cannot be empty${NC}"
        exit 1
    fi
    
    echo "$API_KEY" | gh secret set ANTHROPIC_API_KEY
    echo -e "${GREEN}✓ ANTHROPIC_API_KEY secret added successfully${NC}"
fi

echo ""
echo "========================================"
echo "  Setup Complete!"
echo "========================================"
echo ""
echo -e "${GREEN}✓${NC} Security review workflow is configured"
echo -e "${GREEN}✓${NC} The workflow will run automatically on all pull requests"
echo ""
echo "Next steps:"
echo "1. Commit the workflow files to your repository"
echo "2. Create a pull request to test the security review"
echo "3. Check the PR comments for security findings"
echo ""
echo "Configuration files:"
echo "- .github/workflows/security-review.yml (main workflow)"
echo "- .github/security-review-config.yml (configuration)"
echo ""
echo "For more information, see the README.md file."