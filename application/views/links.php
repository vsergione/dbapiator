<style>
	code{
		background: yellow;
	}
	.def li code:nth-child(1){
		color: red;
	}
	.def li code:nth-child(2){
		color: navy;
	}
	.def li code:nth-child(3){
		color: #8080ff;
	}
</style>
<ul>
	<li>
		<h3>Config</h3>
		<ul>
			<li><a href="configgen/">Config Generator</a></li>
		</ul>
	</li>
	<li>
		<h3>API</h3>
		<h4>Basic request</h4>
		<ul id="list">
			<li><a href="api/v1/poliapitest/users/">GET table records</a><br>
			<code>GET /poliapi/index.php/api/v1/{DATABASENAME}/{TABLENAME}/</code></li>
			<li><a href="api/v1/poliapitest/users/1">GET table record by ID(1)</a><br>
			<code>GET /poliapi/index.php/api/v1/{DATABASENAME}/{TABLENAME}/{RECORD_ID}</code></li>
			<li><a href="api/v1/poliapitest/users/1/groups">GET linkedTable Records of table record by ID(1)</a><br>
			<code>GET /poliapi/index.php/api/v1/{DATABASENAME}/{TABLENAME}/{RECORD_ID}/{LINKED_TABLE}</code></li>
		</ul>
		<h4>Options</h4>
		<p>Are separated between each other by apmersand & symbol</p>
		<ul>
			<li>
				<h5>Filtering</h5>
				<code>?filter={fieldName}[operator]{value},{tablename}.{fieldname}[operator]{value}...</code>
				<dl>
					<dt>fieldName</dt>
					<dd>definition: <code>tableName.fieldName</code>; eg: users.username</dd>
					<dt>operator</dt>
					<dd>can be:
						<ul class="def">
							<li><code>==</code> equal; <code>users.firstName=Joe</code> translates to <code>users.firstName="Joe"</code></li>
							<li><code>~=</code> LIKE;  <code>users.firstName~=Joe</code> translates to <code>users.firstName LIKE "%Joe"</code></li>
							<li><code>=~</code> LIKE;  <code>users.firstName=~Joe</code> translates to <code>users.firstName LIKE "Joe%"</code></li>
							<li><code>~=~</code> LIKE; <code>users.firstName~=~Joe</code> translates to <code>users.firstName LIKE "%Joe%"</code></li>
							<li><code>&lt;=</code> smaller or equal then ; <code></code> translates to <code>users.age <= 1</code></li>
							<li><code>&lt;</code> smaller then ; <code>users.age&lt;1</code> translates to <code>users.age&lt;1</code></li>
							<li><code>&gt;=</code> greater or equal then ; <code>users.age&gt;=1</code> translates to <code>users.age &gt;= 1</code></li>
							<li><code>&gt;</code> greater then ; <code>users.age>1</code> translates to <code>users.age > 1</code></li>
							<li><code>&gt;&lt;</code> IN; <code>users.firstName&gt;&lt;Joe;Mary</code> translates to <code>users.firstName IN ("Joe","Mary")</code></li>
							<li></li>
							<li>Adding <code>!</code> in front of the operator negates the operation. <code>users.firstName!&gt;&lt;Joe;Mary</code> translates to <code>users.firstName NOT IN ("Joe","Mary")</code></li>
						</ul>
						
					</dd>
				</dl>
				
			</li>
			<li>
				<h5>Field selection</h5>
				<code>field=users.firsName,users.lastName...</code>
			</li>
			<li>
				<h5>Sorting</h5>
				<code>sort=+users.firsName,-users.lastName...</code> translates into <code>ORDER BY users.firstName ASC, users.lastName DESC</code>
			</li>
			<li>
				<h5>Pagging</h5>
				<code>offset=20&limit=10</code>  translates into <code>LIMIT 20,10</code>
			</li>
		</ul>
		
	</li>
</ul>

