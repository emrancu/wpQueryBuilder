# wpQueryBuilder 
 <div id="QueryBuilder"></div>
 <p>wpQueryBuilder provides a convenient, fluent interface to run
                database queries. It can be used to perform most database operations in your application. </p>
 
# Instalation
<p> via Composer:  <code>composer require alemran/wp-query-builder</code> </p>
  # Example 
  
   <pre class="mt-2"><code> 
    require 'vendor/autoload.php';
    
    use wpQueryBuilder\DB;
    
    DB::table('demo')->first()
</code></pre>
<br>
<b>DB Functions </b>
<ol>
<li>Retrieving A Single Row<br><code>  DB::table('users')->first();</code></li>
<li>  Retrieving A multiple Row <br><code>  DB::table('users')->get();</code></li>
</ol>

 <ol>
 <li><code>where($column, $value )</code> :
 

 <pre class="mt-2"><code>DB::table('users')
     ->where('id', 1)
     ->get()</code></pre>

  <pre class="mt-2"><code>DB::table('users')
     ->where(function($query){
       $query->where('id', 1);
       $query->orWhere('name', "name");
     })
     ->get()</code></pre>

 </li>
  <li><code>orWhere($column, $value)</code> :
 <pre class="mt-2"><code>DB::table('users')
     ->where('id', 1)
     ->orWhere('name', "name")
     ->get()</code></pre>

 <pre class="mt-2"><code>DB::table('users')
     ->where('id', 1)
     ->orWhere(function($query){
         $query->where('field', 'value);
         $query->where('field', 'value);
         })
     ->first()</code></pre>

 </li>
 <li><code>whereRaw($query)</code> :
 <pre class="mt-2"><code>DB::table('users')
     ->whereRaw('id = 1')
     ->first()</code></pre>
    </li>

  <li><code>orWhereRaw($query)</code> :
  <pre class="mt-2"><code>DB::table('users')
     ->whereRaw('id = 1')
     ->orWhereRaw('id = 1')
     ->first()</code></pre>
 </li>


 <li><code>orderBy($columns, $direction)</code> :
 <pre class="mt-2"><code>DB::table('users')
     ->orderBy('id', 'desc')</code></pre>
                    <pre class="mt-2"><code>DB::table('users')
     ->orderBy('id,name', 'desc')</code></pre>
 </li>

   <li><code>groupBy($columns)</code> :
 <pre class="mt-2"><code>DB::table('users')
     ->groupBy('id')</code></pre>
                    <pre class="mt-2"><code>DB::table('users')
     ->groupBy('id,name')</code></pre>
 </li>


  <li><code>limit($number)</code> :
 <pre class="mt-2"><code>DB::table('users')
     ->where('id', 1)
     ->limit(number)->get()</code></pre>
  </li>

 <li><code>offset($number)</code> :
 <pre class="mt-2"><code>DB::table('users')
     ->where('id', 1)
     ->limit(number)->offset(number)->get()</code></pre>
 </li>

<li><code>select($fields)</code> :
<pre class="mt-2"><code>DB::table('users')
     ->select('id,name')
        ->get()</code></pre>
                </li>
                <li><code>insert($data)</code> :
                    <pre class="mt-2"><code>DB::table('users')
     ->insert(['name' => "demo"])</code></pre>
 </li>
<li><code>update($data,$where)</code> :
<pre class="mt-2"><code>DB::table('users')
     ->where('id', 1)
     ->update(['name' => "demo"])</code></pre>
</li>
<li><code>delete($where)</code> :
<pre class="mt-2"><code>DB::table('users')
     ->where('id', 1)
     ->delete()</code></pre>
</li>

<li><code>join($table, $first, $operator = null, $second = null) (INNER JOIN)</code>:
<pre class="mt-2"><code>DB::table('demo_notes as n')
        ->join('demo_users as u', 'u.id', '=', 'n.user_id')
        ->first()
</code></pre>
<pre class="mt-2"><code>DB::table('demo_notes as n')
        ->join('demo_users as u', function($query){
          $query->on( 'u.id', '=', 'n.user_id')
          $query->orOn( 'u.id', '=', 'n.user_id')
        })
        ->first()
</code></pre>

 <pre class="mt-2"><code>DB::table('demo_notes as n')
        ->join('demo_users as u', function($query) use($request){
          $query->on( 'u.id', '=', 'n.user_id')
          $query->onWhere( 'u.id', '=', $request->id)
        })
        ->first()
</code></pre>


 <blockquote>Note: Must use table alias for using join or leftJoin.</blockquote>
</li>
<li>
<code>leftJoin($table, $first, $operator = null, $second = null) (LEFT JOIN)</code>: <b> Same as
                        join()</b>
</li>

 <li><code>transaction()</code>:
<pre class="mt-2"><code>DB::startTransaction(function(){
    DB::table('demo_notes')
      ->insert([
         "note" => "Hello",
       ]);
})</code></pre>

<pre class="mt-2"><code>DB::startTransaction(function(DB $query){
    $query->table('demo_notes')
      ->insert([
         "note" => "Hello",
       ]);
})</code></pre>

 </li>
 </ol>
