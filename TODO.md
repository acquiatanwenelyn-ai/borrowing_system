# TODO: Implement Date-Based Availability for Borrowing Transactions

## Tasks
- [ ] Modify views/transactions.php to add date-based availability check during transaction creation
  - [ ] Add logic to calculate committed quantities for overlapping dates
  - [ ] Check if available >= requested for each item
  - [ ] Prevent transaction creation if any item unavailable
- [ ] Test the implementation
  - [ ] Create transactions with overlapping dates
  - [ ] Verify conflicts are avoided
  - [ ] Test early returns adjust availability
