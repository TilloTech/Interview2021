# Issues with the App

Here's a list of all the problems with the application — solve as many as you can!

There will be automated tests to check each of the issues is fixed.

The issues here are roughly in order of difficulty.

## 1. Can't see the order details

When you click the '->' button in the table, nothing appears to happen.

## 2. Clickable email and telephone numbers

The phone number and email addresses on each order should contain 'tel:' and 'mailto:' prefixes in their links but they do not.

## 3. Broken website link

The website link in the order details panel doesn't work.

## 4. Capitalise those titles!

The title of each order should be capitalised. This should be done in the table and in the order details panel.

## 5. Loading message

The 'Loading...' message in App.vue doesn't appear while the data is loading.

## 6. Tricky pagination

It's possible to click to view page numbers before page 1 (negative page numbers) and after the last page. Causing a the table to go blank.

## 7. Money formatting

The order prices aren't formatted correctly as currencies. Check the test for this issue to help get the format correct!

## 8. Date formatting

The order dates are quite long and not very easy to read. Make sure they're formatted DD/MM/YYYY.

## 9. The Search Bar (BONUS)

There's no search bar to help look up orders in the table. Add a search bar that allows the user to enter search for Order IDs. _There are no tests for this issue — add some in the tests directory!_
