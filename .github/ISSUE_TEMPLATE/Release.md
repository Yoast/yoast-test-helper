---
name: Release
about: Release a new version of Yoast Test Helper.
---

## Release version [VERSION]

* [ ] Check out `develop`.
* [ ] Update the changelog in `readme.txt` with the relevant entries, you should be able to find them based on the pull requests in this release's milestone.
* [ ] Update `README.md` if needed, for instance for new features.
* [ ] Merge `develop` into `master`.
* [ ] Set the version using `grunt set-version --new-version=[VERSION]` (e.g., `grunt set-version --new-version=1.1.0`) & `grunt update-version`.
* [ ] Commit & push the changes: `git commit -m "Updating version to [VERSION]" && git push`.
* [ ] Make the tag, e.g. `git tag -a 1.1.0 -m "1.1.0"`.
* [ ] Push the tag: `git push --tags`.
* [ ] Run `grunt artifact` - this will create `yoast-test-helper.zip`.
* [ ] Test the zip locally.
* [ ] Run `grunt wp_deploy:master` to release the plugin to WordPress.org.
* [ ] [Create a GitHub release](https://github.com/Yoast/yoast-test-helper/releases), paste the changelog to the notes and attach the zip.
* [ ] [Create a new milestone](https://github.com/Yoast/yoast-test-helper/milestones/new) for the next release.
* [ ] Merge `master` back to `develop`.
