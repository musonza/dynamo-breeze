# Contributing to **DynamoBreeze**

First off, thank you for considering contributing to **DynamoBreeze**. It's people like you that make **DynamoBreeze** such a great tool.

## Licensing

This project is licensed under the MIT license. By contributing to this project, you agree that your contributions will be licensed under its MIT license.

## Submitting a Pull Request

1. Fork the project and create your branch from `main`.
2. If you've added code that should be tested, add tests.
3. If you've changed APIs, update the documentation.
4. Ensure the test suite passes.
5. Make sure your code lints (use `composer test:lint-fix`).
6. Issue that pull request!

## Reporting Bugs or Suggesting Enhancements

We use GitHub issues to track public bugs. Please ensure your description is clear and has sufficient instructions to be able to reproduce the issue.

If this is a feature request, please ensure your description clearly states the problem you’re trying to solve as well as a detailed explanation of why you believe this is a problem that needs to be addressed.

## Coding Conventions

Start reading our code, and you'll get the hang of it. We optimize for readability:

- We adhere to PSR standards: PSR-1, PSR-4, and PSR-12.
- We keep the code simple and readable.
- We comment as needed but prefer readable code over excessive comments.

## Development Environment

To set up your development environment, there are a few key requirements you need to meet to ensure consistency and functionality:

1. **Laravel Version:** Make sure you're using the same Laravel version that the package requires or is developed on. This is crucial to prevent potential compatibility issues that might arise from version discrepancies.

2. **DynamoDB Local for Feature Tests:** Our package uses Amazon DynamoDB for certain features, and we require DynamoDB Local for running feature tests locally. Please install and configure DynamoDB Local according to Amazon's official guide. This allows you to test the relevant features in an environment that mimics the production setup without incurring additional costs or network overhead.

3. **Additional Software Requirements:** Be aware of and adhere to any additional software prerequisites or dependencies that the package might have.

## Community

Remember, the key to fostering a healthy open source community is good communication. Always be respectful and appreciate the effort others have put into contributions.

### GitHub Discussions

For general questions, brainstorming, and open-ended discussion, please use our [GitHub Discussions](https://github.com/musonza/dynamo-breeze/discussions/2). This is a great place to start socializing ideas, seek help from other community members, or discuss broader topics related to the project. Remember, a fresh perspective can be invaluable, and your insights might just spark the next big feature or improvement.
